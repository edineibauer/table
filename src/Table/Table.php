<?php
/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 29/09/2017
 * Time: 11:01
 */

namespace TableList;

use ConnCrud\Read;
use ConnCrud\TableCrud;
use Entity\Entity;
use EntityForm\Metadados;
use Helpers\Template;

class Table
{
    private $entity;
    private $link;
    private $result;

    public function __construct($entity = null)
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
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getShow()
    {
        return $this->start();
    }

    /**
     * @return mixed
     */
    public function show()
    {
        echo $this->getShow();
    }

    private function start()
    {
        foreach (Metadados::getDicionario($this->entity) as $i => $data) {
            if(in_array($data['format'], ["title", "date", "datetime"]))
                $dados['header'][] = $data['nome'];
        }
        $dados['entity'] = $this->entity;

        $read = new Read();
        $read->exeRead(PRE . $this->entity);
        $dados['total'] = $read->getRowCount();

        $template = new Template("table-list");
        $template->setFolder(PATH_HOME . "tpl");
        return $template->getShow("tableList", $dados);
    }
}
