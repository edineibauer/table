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

    /**
     * Table constructor.
     * @param string $entity
     */
    public function __construct(string $entity = "")
    {
        $this->setEntity($entity);
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
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
        if(!$this->fields) {
            $relevants = Metadados::getRelevantAll($this->entity);
            foreach (Metadados::getDicionario($this->entity, true) as $i => $data) {
                if (in_array($data['format'], $relevants) && $data['form'] && count($this->fields['nome']) < 5) {
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
        $read = new Read();
        $read->exeRead(PRE . $this->entity, $this->getWhere(new Dicionario($this->entity)));
        $dados['total'] = $read->getRowCount();
        $dados['entity'] = $this->entity;
        $dados['header'] = $this->getFields()['nome'];

        $template = new Template("table");
        return $template->getShow("table", $dados);
    }

    /**
     * @param Dicionario $d
     * @param mixed $filter
     * @return string
     */
    protected function getWhere(Dicionario $d, $filter = null): string
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
        if (!empty($general[$this->entity]['owner'])) {
            $entityRelation = $general[$this->entity]['owner'][0];
            $column = $general[$this->entity]['owner'][1];
            $userColumn = $general[$this->entity]['owner'][2];
            $tableRelational = PRE . $entityRelation . "_" . $this->entity . "_" . $column;

            $read = new Read();
            $read->exeRead($entityRelation, "WHERE {$userColumn} = :user", "user={$_SESSION['userlogin']['id']}");
            if($read->getResult()) {
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

        if ($filter) {
            foreach ($filter as $item => $value)
                $where .= " && (" . ($item === "title" ? $d->getRelevant()->getColumn() : $d->search($item)->getColumn()) . " LIKE '%{$value}%' || id LIKE '%{$value}%')";

            /*
            foreach (array_map('trim', $this->filter) as $column => $value) {
                if (!empty($value)) {
                    foreach (array_map("trim", explode("&&", $value)) as $or) {

                        $where .= (empty($where) ? "WHERE (" : ") && (");
                        $c = "";
                        foreach (array_map("trim", explode("||", $or)) as $and) {
                            $comand = $this->checkCommandWhere($and);
                            $comand['value'] = strip_tags($comand['value']);

                            if (!empty($comand['value'])) {
                                $where .= $c . strip_tags($column) . " " . $this->commandWhere($comand);
                                $c = " || ";
                            }
                        }
                    }
                }
            }
            $where .= (!empty($where) ? ")" : "");
            */
        }
        return $where;
    }
}
