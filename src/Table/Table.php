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
        foreach (Metadados::getDicionario($entity) as $i => $data) {
            if (in_array($data['format'], ["title", "status", "text", "date", "datetime", "email", "valor", "cpf", "cep", "cnpj", "ie", "rg", "time", "week", "month", "year"])) {
                $dados['header'][] = $data['nome'];
                $dados['meta'][] = $data['format'];
            }
        }
        if (!empty($dados['header']))
            $dados['header'] = self::filtroHeader($dados['header'], $dados['meta']);

        unset($dados['meta']);
        $dados['entity'] = $entity;

        $read = new Read();
        $read->exeRead(PRE . $entity);
        $dados['total'] = $read->getRowCount();

        $template = new Template("table");
        return $template->getShow("table", $dados);
    }

    /**
     * Reduz o número de valores da tabela caso sejam muitos a aparecerem
     *
     * @param array $dados
     * @param array $meta
     * @return array
     */
    private static function filtroHeader(array $dados, array $meta): array
    {
        if (count($dados) > 5) {
            foreach (array_reverse($dados) as $i => $data) {
                if (count($dados) > 5 && in_array($meta[$i], ["text", "ie", "rg"]))
                    unset($dados[$i]);
            }

            if (count($dados) > 5) {
                foreach (array_reverse($dados) as $i => $data) {
                    if (count($dados) > 5 && in_array($meta[$i], ["cpf", "cep", "cnpj", "time", "week", "month", "year"]))
                        unset($dados[$i]);
                }
            }

            if (count($dados) > 5) {
                foreach (array_reverse($dados) as $i => $data) {
                    if (count($dados) > 5 && in_array($meta[$i], ["status", "date", "email"]))
                        unset($dados[$i]);
                }
            }
        }

        return $dados;
    }
}
