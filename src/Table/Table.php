<?php

namespace Table;

use ConnCrud\Read;
use EntityForm\Dicionario;
use EntityForm\Metadados;
use Helpers\Template;

class Table
{
    private $entity;
    private $fields;
    private $search;
    private $buttons;

    /**
     * Table constructor.
     * @param string $entity
     */
    public function __construct(string $entity = "")
    {
        $this->setEntity($entity);
        $this->buttons = [
            "edit" => true,
            "delete" => true,
            "copy" => true,
            "status" => true
        ];
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function toggleButton(string $button)
    {
        if (isset($this->buttons[$button]))
            $this->buttons[$button] = !$this->buttons[$button];
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param mixed $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    protected function getFields()
    {
        if (empty($this->fields)) {
            $relevants = Metadados::getRelevantAll($this->entity);
            foreach (Metadados::getDicionario($this->entity, true) as $i => $data) {
                if (in_array($data['format'], $relevants) && $data['form'] && (empty($this->fields) || count($this->fields['nome']) < 5)) {
                    $this->fields['nome'][] = $data['nome'];
                    $this->fields['column'][] = $data['column'];
                    $this->fields['format'][] = $data['format'];
                }
            }
        }
        return $this->fields;
    }

    /**
     * @param mixed $entity
     * @return string
     */
    public function getShow($entity = null): string
    {
        if ($entity)
            $this->setEntity($entity);

        return $this->getTable();
    }

    /**
     * @param string $entity
     */
    public function show(string $entity)
    {
        echo $this->getShow($entity);
    }

    /**
     * @return string
     */
    protected function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return string
     */
    private function getTable(): string
    {
        $d = new Dicionario($this->entity);
        $read = new Read();
        $read->exeRead(PRE . $this->entity, $this->getWhere($d));
        $dados['total'] = $read->getRowCount();
        $dados['entity'] = $this->entity;
        $dados['entityName'] = ucwords(str_replace(["_", "-", "  "], [" ", " ", " "], $this->entity));
        $dados['header'] = $this->getFields()['nome'];
        $dados['status'] = !empty($st = $d->getInfo()['status']) ? $d->search($st)->getNome() : null;
        $dados['buttons'] = $this->getButtons();

        $template = new Template("table");
        return $template->getShow("table", $dados);
    }

    /**
     * @param Dicionario $d
     * @param array|null $filter
     * @return string
     */
    protected function getWhere(Dicionario $d, array $filter = null): string
    {
        $where = "WHERE id > 0";

        //filtro de tabela por owner
        if ($idP = $d->getInfo()['publisher']) {
            $metaOwner = $d->search($idP);
            if ($metaOwner->getFormat() === "owner" && $_SESSION['userlogin']['setor'] > 1)
                $where .= " && " . $metaOwner->getColumn() . " = {$_SESSION['userlogin']['id']}";
        }

        //filtro de tabela por lista de IDs
        $general = json_decode(file_get_contents(PATH_HOME . "entity/general/general_info.json"), true);
        if (!empty($general[$this->entity]['owner']) || !empty($general[$this->entity]['ownerPublisher'])) {
            foreach (array_merge($general[$this->entity]['owner'] ?? [], $general[$this->entity]['ownerPublisher'] ?? []) as $item) {
                $entityRelation = $item[0];
                $column = $item[1];
                $userColumn = $item[2];
                $tableRelational = PRE . $entityRelation . "_" . $this->entity . "_" . $column;

                $read = new Read();
                $read->exeRead($entityRelation, "WHERE {$userColumn} = :user", "user={$_SESSION['userlogin']['id']}");
                if ($read->getResult()) {
                    $idUser = $read->getResult()[0]['id'];

                    $read->exeRead($tableRelational, "WHERE {$entityRelation}_id = :id", "id={$idUser}");
                    if ($read->getResult()) {
                        $where .= " && (id = 0";
                        foreach ($read->getResult() as $item)
                            $where .= " || id = {$item["{$this->entity}_id"]}";
                        $where .= ")";
                    } else {
                        $where = "WHERE id < 0";
                    }
                }
            }
        }

        if (!empty($this->search) && is_string($this->search)) {
            $where .= " && (";
            foreach (['identifier', 'title', 'link', 'email', 'tel', 'cpf', 'cnpj', 'cep'] as $item) {
                if (!empty($d->getInfo()[$item]) && !empty($c = $d->search($d->getInfo()[$item]))) {
                    $where .= (isset($firstSearch) ? " || " : "") . PRE . $this->entity . ".{$c->getColumn()} LIKE '%{$this->search}%'";
                    $firstSearch = 1;
                }
            }
            $where .= ")";

        } elseif ($filter) {
            foreach ($filter as $item => $value)
                $where .= " && (" . ($item === "title" ? $d->getRelevant()->getColumn() : $d->search($item)->getColumn()) . " LIKE '%{$value}%' || id LIKE '%{$value}%')";
        }

        return $where;
    }
}
