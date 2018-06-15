<?php

namespace Table;

use ConnCrud\Read;
use Entity\Entity;
use EntityForm\Dicionario;
use EntityForm\Metadados;
use Helpers\Date;
use Helpers\DateTime;
use Helpers\Template;

class TableData extends Table
{
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
            parent::setEntity($entity);

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
     * @param string $relation
     */
    public function setRelation(string $relation)
    {
        parent::setRelation($relation);
    }

    /**
     * @param string $column
     */
    public function setColumn(string $column)
    {
        parent::setColumn($column);
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        parent::setType($type);
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        parent::setId($id);
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
        if (parent::getEntity()) {

            $d = new Dicionario(parent::getEntity());
            $dicionario = Metadados::getDicionario(parent::getEntity(), true);
            $relevants = Metadados::getRelevantAll(parent::getEntity());

            $this->pagina = $this->pagina < 2 ? 1 : $this->pagina;
            $this->offset = ($this->pagina * $this->limit) - $this->limit;
            $where = parent::getWhere($d, $this->filter);
            $this->total = $this->getMaximo($where);

            $read = new Read();
            $read->exeRead(PRE . parent::getEntity(), $where . $this->getOrder());
            if ($read->getResult()) {
                $this->count = $read->getRowCount();
                $this->response = true;

                $dados['names'] = [];
                foreach ($dicionario as $data) {
                    if (in_array($data['format'], $relevants) && $data['form'] && count($dados['names']) < 6)
                        $dados['names'][] = $data['column'];
                }

                $dados['entity'] = parent::getEntity();
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
            if (in_array($di['format'], $relevants)) {
                foreach ($data as $i => $datum) {

                    $data[$i]['permission'] = Entity::checkPermission(parent::getEntity(), $datum['id']);
                    foreach ($datum as $column => $value) {
                        if ($column === $di['column']) {
                            if(!empty($value)) {
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
                                    case 'status':
                                    case 'boolean':
                                        $data[$i][$column] = $value ? "<span class='color-green tag'>ON</span>" : "<span class='color-orange tag color-text-white'>OFF</span>";
                                        break;
                                }
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
        if (!empty($value)) {
            $value = json_decode($value, true);

            if (preg_match('/^image\//i', $value[0]['type']))
                return "<img src='{$value[0]['url']}' title='{$value[0]['name']}' height='30' style='height: 30px;width: auto' />";

            return "";
        }

        return "";
    }

    /**
     * @param string
     * @return int
     */
    public function getMaximo(string $where): int
    {
        $read = new Read();
        $read->exeRead(PRE . parent::getEntity(), $where);
        return (int)$read->getRowCount();
    }

    /*
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
        }*/

    private function getOrder()
    {
        $order = "";
        $order .= ($this->order ? " ORDER BY {$this->order}" . ($this->orderAsc ? "" : " DESC") : "ORDER BY id DESC");
        $order .= ($this->limit || $this->offset ? " LIMIT " . ($this->offset ? "{$this->offset}, " : "") . ($this->limit ?? 1000) : "");

        return $order;
    }
}