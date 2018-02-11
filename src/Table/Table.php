<?php
namespace Table;

use ConnCrud\Read;
use EntityForm\Metadados;
use Helpers\Template;

class Table
{
    /**
     * @param string $entity
     * @return string
     */
    public static function getShow(string $entity) :string
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
    private static function getTable(string $entity) :string
    {
        foreach (Metadados::getDicionario($entity) as $i => $data) {
            if(in_array($data['format'], ["title", "date", "datetime"]))
                $dados['header'][] = $data['nome'];
        }
        $dados['entity'] = $entity;

        $read = new Read();
        $read->exeRead(PRE . $entity);
        $dados['total'] = $read->getRowCount();

        $template = new Template("table");
        return $template->getShow("table", $dados);
    }
}
