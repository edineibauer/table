<?php

namespace Table;

use ConnCrud\Read;
use EntityForm\Dicionario;
use EntityForm\Metadados;
use Helpers\Template;

class Table
{
    private $entity;
    private $listaId = 0;

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
     * @param array $listaId
     */
    public function setListaId(array $listaId)
    {
        $this->listaId = $listaId;
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
        if ($this->listaId !== 0) {
            if (!empty($this->listaId))
                $where .= implode(" && id = ", $this->listaId);
            else
                $where = "WHERE id < 0";
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
