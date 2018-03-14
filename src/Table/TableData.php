<?php

namespace Table;

use ConnCrud\Read;
use EntityForm\Metadados;
use Helpers\Date;
use Helpers\DateTime;
use Helpers\Template;

class TableData
{
    private $entity;
    private $limit;
    private $pagina;
    private $offset;
    private $order;
    private $filter;
    private $dados;
    private $orderAsc = false;
    private $response = false;
    private $total;
    private $count = 0;

    public function __construct($entity)
    {
        if ($entity)
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
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @param mixed $pagina
     */
    public function setPagina($pagina)
    {
        $this->pagina = $pagina;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @param bool $orderAsc
     */
    public function setOrderAsc(bool $orderAsc)
    {
        $this->orderAsc = $orderAsc;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return mixed
     */
    public function getDados()
    {
        $this->start();
        return $this->dados;
    }

    /**
     * @return bool
     */
    public function isResponse(): bool
    {
        $this->start();
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getPagination()
    {
        return (int)ceil($this->total / $this->limit);
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    private function start()
    {
        if ($this->entity) {

            $dicionario = Metadados::getDicionario($this->entity, true);
            $relevants = Metadados::getRelevantAll($this->entity);

            $this->pagina = $this->pagina < 2 ? 1 : $this->pagina;
            $this->offset = ($this->pagina * $this->limit) - $this->limit;
            $where = $this->getWhere($dicionario);
            $this->getMaximo($where);

            $read = new Read();
            $read->exeRead(PRE . $this->entity, $where . $this->getOrder());
            if ($read->getResult()) {
                $this->count = $read->getRowCount();
                $this->response = true;

                $dados['names'] = [];
                foreach ($dicionario as $data) {
                    if (in_array($data['format'], $relevants) && count($dados['names']) < 6)
                        $dados['names'][] = $data['column'];
                }

                $dados['entity'] = $this->entity;
                $dados['values'] = $this->dataMask($read->getResult(), $dicionario, $relevants);

                $template = new Template('table');
                $this->dados = $template->getShow("tableContent", $dados);
            }

        }
    }

    private function dataMask($data, $dic, array $relevants)
    {
        $datetime = new DateTime();
        $date = new Date();
        foreach ($dic as $di) {
            if(in_array($di['format'], $relevants)) {
                foreach ($data as $i => $datum) {
                    foreach ($datum as $column => $value) {
                        if ($column === $di['column']){
                            switch ($di['format']) {
                                case 'datetime':
                                    $data[$i][$column] = $datetime->getDateTime($value, "H:i\h d/m/y");
                                    break;
                                case 'date':
                                    $data[$i][$column] = $date->getDate($value, "d/m/y");
                                    break;
                                case 'source':
                                    $data[$i][$column] = $this->getSource($value);
                                    break;
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $data;
    }

    private function getSource($value)
    {
        $value = json_decode($value, true);

        switch ($value[0]['type']) {
            case 'image/jpeg':
                return "<img src='{$value[0]['url']}' title='{$value[0]['name']}' height='30' style='height: 30px;width: auto' />";
                break;
        }

        return "";
    }

    public function getMaximo($where)
    {
        $read = new Read();
        $read->exeRead(PRE . $this->entity, $where);
        $this->total = $read->getRowCount();
    }

    /**
     * @param array $dicionario
     * @return string
     */
    private function getWhere(array $dicionario) :string
    {
        $where = "WHERE id > 0";
        if ($this->filter) {

            $info = Metadados::getInfo($this->entity);
            $relevant = Metadados::getRelevant($this->entity);

            foreach ($this->filter as $item => $value) {
                $where .= " && {$dicionario[$item === "title" ? $relevant : $info[$item]]['column']} LIKE '%{$value}%'";
            }
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

    private function commandWhere($comand)
    {
        switch ($comand['comando']) {
            case '=':
                return ($comand['negado'] ? "!" : "") . "= '{$comand['value']}'";
                break;
            case '>':
                return ($comand['negado'] ? "<= " : "> ") . "'{$comand['value']}'";
                break;
            case '<':
                return ($comand['negado'] ? ">= " : "< ") . "'{$comand['value']}'";
                break;
            case '>=':
                return ($comand['negado'] ? "< " : ">= ") . "'{$comand['value']}'";
                break;
            case '<=':
                return ($comand['negado'] ? "> " : "<= ") . "'{$comand['value']}'";
                break;
            case '^':
                return ($comand['negado'] ? "NOT " : "") . "LIKE '{$comand['value']}%'";
                break;
            case '$':
                return ($comand['negado'] ? "NOT " : "") . "LIKE '%{$comand['value']}'";
                break;

            default:
                return ($comand['negado'] ? "NOT " : "") . "LIKE '%{$comand['value']}%'";
        }
    }

    private function checkCommandWhere($value)
    {
        $negado = false;
        $comand = "Like";

        if (preg_match('/^!/i', $value)) {
            $negado = true;
            $value = substr($value, 1);
        }

        if (preg_match('/^=/i', $value)) {
            $comand = "=";
            $value = substr($value, 1);
        } elseif (preg_match('/^>/i', $value)) {
            $comand = ">";
            $value = substr($value, 1);
        } elseif (preg_match('/^</i', $value)) {
            $comand = "<";
            $value = substr($value, 1);
        } elseif (preg_match('/^>=/i', $value)) {
            $comand = ">=";
            $value = substr($value, 2);
        } elseif (preg_match('/^<=/i', $value)) {
            $comand = "<=";
            $value = substr($value, 2);
        } elseif (preg_match('/^\^/i', $value)) {
            $comand = "^";
            $value = substr($value, 1);
        } elseif (preg_match('/\$$/i', $value)) {
            $comand = "$";
            $value = substr($value, 0, -1);
        }

        return array("negado" => $negado, "comando" => $comand, "value" => $value);
    }

    private function getOrder()
    {
        $order = "";
        $order .= ($this->order ? " ORDER BY {$this->order}" . ($this->orderAsc ? "" : " DESC") : "ORDER BY id DESC");
        $order .= ($this->limit || $this->offset ? " LIMIT " . ($this->offset ? "{$this->offset}, " : "") . ($this->limit ?? 1000) : "");

        return $order;
    }
}