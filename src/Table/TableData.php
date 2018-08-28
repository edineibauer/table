<?php

namespace Table;

use ConnCrud\Read;
use Entity\Entity;
use EntityForm\Dicionario;
use Helpers\Check;
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
        parent::__construct($entity);
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
        if (parent::getEntity()) {

            $d = new Dicionario(parent::getEntity());

            $this->pagina = $this->pagina < 2 ? 1 : $this->pagina;
            $this->offset = ($this->pagina * $this->limit) - $this->limit;
            $where = parent::getWhere($d, $this->filter);
            $this->total = $this->getMaximo($where);

            $read = new Read();
            $read->exeRead(PRE . parent::getEntity(), $where . " " . $this->getOrder());
            if ($read->getResult()) {
                $this->count = $read->getRowCount();
                $this->response = true;

                $dados['names'] = parent::getFields()['column'];
                $dados['entity'] = parent::getEntity();
                $dados['values'] = $this->dataMask($read->getResult());
                $dados['buttons'] = $this->getButtons();
                $dados['status'] = !empty($st = $d->getInfo()['status']) ? $d->search($st)->getColumn() : null;

                $template = new Template('table');
                $this->dados = $template->getShow("tableContent", $dados);
            }

        }
    }

    private function dataMask($data)
    {
        $datetime = new DateTime();
        $date = new Date();
        foreach ($data as $i => $datum) {
            $data[$i]['permission'] = Entity::checkPermission(parent::getEntity(), $datum['id']);
            $format = parent::getFields()['format'];
            foreach (parent::getFields()['column'] as $e => $field) {
                switch ($format[$e]) {
                    case 'datetime':
                        $data[$i][$field] = !empty($datum[$field]) ? $datetime->getDateTime($datum[$field], "H:i\h d/m/y") : "";
                        break;
                    case 'date':
                        $data[$i][$field] = !empty($datum[$field]) ? $date->getDate($datum[$field], "d/m/Y") : "";
                        break;
                    case 'source':
                        $data[$i][$field] = $this->getSource($datum[$field]);
                        break;
                    case 'valor':
                        $data[$i][$field] = !empty($datum[$field]) ? "R$" . number_format($datum[$field], 2) : "";
                        break;
                    case 'percent':
                        $data[$i][$field] = !empty($datum[$field]) ? $datum[$field] . "%" : "";
                        break;
                    case 'cpf':
                        $data[$i][$field] = !empty($datum[$field]) ? Check::mask($datum[$field], '###.###.###-##') : "";
                        break;
                    case 'cep':
                        $data[$i][$field] = !empty($datum[$field]) ? Check::mask($datum[$field], '#####-###') : "";
                        break;
                    case 'cnpj':
                        $data[$i][$field] = !empty($datum[$field]) ? Check::mask($datum[$field], '##.###.###/####-##') : "";
                        break;
                    case 'ie':
                        $data[$i][$field] = !empty($datum[$field]) ? Check::mask($datum[$field], '###.###.###.###') : "";
                        break;
                    case 'tel':
                        $lenght = strlen($datum[$field]);
                        $mask = ($lenght === 11 ? '(##) #####-####' : ($lenght === 10 ? '(##) ####-####' : ($lenght === 9 ? '#####-####' : '####-####')));
                        $data[$i][$field] = !empty($datum[$field]) ? Check::mask($datum[$field], $mask) : "";
                        break;
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