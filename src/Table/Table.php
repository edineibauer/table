<?php

namespace Table;

use ConnCrud\Read;
use EntityForm\Dicionario;
use EntityForm\Metadados;
use Helpers\Template;

class Table
{
    /**
     * @param string $entity
     * @return string
     */
    public static function getShow(string $entity): string
    {
        return self::getTable($entity);
    }

    /**
     * @param string $entity
     */
    public static function show(string $entity)
    {
        echo self::getShow($entity);
    }

    /**
     * @param string $entity
     * @return string
     */
    private static function getTable(string $entity): string
    {
        $dados['header'] = [];
        $relevants = Metadados::getRelevantAll($entity);
        foreach (Metadados::getDicionario($entity, true) as $i => $data) {
            if (in_array($data['format'], $relevants) && $data['form'] && count($dados['header']) < 6) {

                $dados['header'][] = $data['nome'];
                $dados['meta'][] = $data['format'];
            }
        }

        unset($dados['meta']);
        $dados['entity'] = $entity;

        $where = self::getWhere(new Dicionario($entity));
        $read = new Read();
        $read->exeRead(PRE . $entity, $where);
        $dados['total'] = $read->getRowCount();

        $template = new Template("table");
        return $template->getShow("table", $dados);
    }

    /**
     * @param Dicionario $d
     * @param mixed $filter
     * @return string
     */
    protected static function getWhere(Dicionario $d, $filter = null): string
    {
        $where = "WHERE id > 0";

        if($idP = $d->getInfo()['publisher']){
            $metaOwner = $d->search($idP);
            if($metaOwner->getFormat() === "owner" && $_SESSION['userlogin']['setor'] > 1)
                $where .= " && " . $metaOwner->getColumn() . " = {$_SESSION['userlogin']['id']}";
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
