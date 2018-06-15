<?php

namespace Table;

use ConnCrud\Read;
use EntityForm\Dicionario;
use EntityForm\Metadados;
use Helpers\Template;

class Table
{
    private $entity;
    private $relation;
    private $column;
    private $type;
    private $id;

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
     * @param string $relation
     */
    public function setRelation(string $relation)
    {
        $this->relation = $relation;
    }

    /**
     * @param string $column
     */
    public function setColumn(string $column)
    {
        $this->column = $column;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
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
        $dados['header'] = [];
        $relevants = Metadados::getRelevantAll($this->entity);
        foreach (Metadados::getDicionario($this->entity, true) as $i => $data) {
            if (in_array($data['format'], $relevants) && $data['form'] && count($dados['header']) < 6) {
                $dados['header'][] = $data['nome'];
            }
        }

        $dados['entity'] = $this->entity;
        $dados['relation'] = $this->relation;
        $dados['column'] = $this->column;
        $dados['type'] = $this->type;
        $dados['id'] = $this->id;

        $where = $this->getWhere(new Dicionario($this->entity));
        $read = new Read();
        $read->exeRead(PRE . $this->entity, $where);
        $dados['total'] = $read->getRowCount();

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
        if (!empty($this->type)) {
            $read = new \ConnCrud\Read();
            if ($this->type === "owner")
                $read->exeRead(PRE . $this->relation . "_" . $this->entity . "_" . $this->column, "WHERE {$this->relation}_id =:id", "id={$this->id}");
            else
                $read->exeRead(PRE . $this->relation . "_" . $this->entity . "_" . $this->column, "WHERE {$this->relation}_id =:id", "id={$this->id}");

            if ($read->getResult()) {
                foreach ($read->getResult() as $item)
                    $where .= " && id = {$item["{$this->entity}_id"]}";
            } else {
                var_dump(PRE . $this->relation . "_" . $this->entity . "_" . $this->column);
                $where = "WHERE id < 0";
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
