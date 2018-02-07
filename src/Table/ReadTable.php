<?php
/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 06/10/2017
 * Time: 16:06
 */

namespace TableList;


use ConnCrud\Read;
use Entity\Entity;
use Helpers\Template;

class ReadTable
{
    private $table;
    private $limit;
    private $pagina;
    private $offset;
    private $order;
    private $filter;
    private $dados;
    private $orderAsc = false;
    private $response = false;
    private $pagination;
    private $count = 0;

    public function __construct($table)
    {
        if ($table) {
            $this->setTable($table);
        }
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
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
    public function getPagination()
    {
        return $this->pagination;
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
        if ($this->table && !$this->dados) {

            $this->pagina = (!$this->pagina || $this->pagina < 2 ? 1 : $this->pagina);
            $this->offset = ($this->pagina * $this->limit) - $this->limit;
            $where = $this->getWhere();
            $order = $this->getOrder();
            $this->pagination = $this->getMaximo($where);

            $read = new Read();
            if(!empty($where) || !empty($order)) {
                $read->exeRead(PRE . $this->table, $where . $order);
            } else {
                $read->exeRead(PRE . $this->table);
            }

            if ($read->getResult()) {
                $this->count = $read->getRowCount();
                $this->response = true;

                $entity = new Entity($this->table);
                $dados = $entity->getMetadados();
                $dados['info']['table'] = $this->table;
                $dados['values'] = $read->getResult();

                $template = new Template('table-list');
                $this->dados = $template->getShow("tableListBody", $dados);
            }

        }
    }

    function getMaximo($where): int
    {
        $read = new Read();
        $read->exeRead(PRE . $this->table, $where);
        return (int)ceil($read->getRowCount() / $this->limit);
    }

    private function getWhere()
    {
        $where = "";
        if ($this->filter) {
            foreach (array_map('trim', $this->filter) as $column => $value) {
                if(!empty($value)) {
                    $column = strip_tags($column);
                    foreach ($this->checkOrValues($value, "&&") as $or) {

                        $where .= (empty($where) ? "WHERE (" : ") && (");
                        $c = "";
                        foreach ($this->checkOrValues($or, "||") as $and) {
                            $comand = $this->checkCommandWhere($and);
                            $comand['value'] = strip_tags($comand['value']);

                            if (!empty($comand['value'])) {
                                $where .= $c . "{$column} " . $this->commandWhere($comand);
                                $c = " || ";
                            }
                        }
                    }
                }
            }
            $where .= (!empty($where) ? ")" : "");
        }
        return $where;
    }

    private function checkOrValues($value, $co)
    {
        return array_map("trim", explode($co, $value));
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
        $order .= ($this->order ? " ORDER BY {$this->order}" . ($this->orderAsc ? "" : " DESC") : "");
        $order .= ($this->limit || $this->offset ? " LIMIT " . ($this->offset ? "{$this->offset}, " : "") . ($this->limit ?? 1000) : "");

        return $order;
    }
}