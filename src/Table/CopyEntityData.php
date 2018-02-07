<?php
/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 06/10/2017
 * Time: 16:06
 */

namespace TableList;

use Entity\Entity;

class CopyEntityData
{
    private $entity;
    private $ids;
    private $result;

    public function __construct($entity = null, $data = null)
    {
        if ($entity) {
            $this->setEntity($entity);
            if ($data) {
                $this->setIds($data);
                $this->start();
            }
        }
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param mixed $ids
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    public function copyEntityData($ids = null)
    {
        if ($ids) {
            $this->setIds($ids);
        }
        $this->start();
    }

    private function start()
    {
        if ($this->entity && $this->ids) {
            $this->result = $this->copyData($this->entity, $this->ids);
        }
    }

    private function copyData($entityName, $ids)
    {
        $entity = new Entity($entityName);
        $info = $entity->getJsonInfoEntity();
        $struct = $entity->getJsonStructEntity();

        $ids = (is_array($ids) && !isset($ids[1]) ? (int) $ids[0] : $ids);

        if (is_array($ids)) {
            $idsCriados = [];
            foreach ($ids as $id) {
                if (is_int($id)) {
                    $dados = $entity->getDataEntity($id);
                    if (isset($dados['id']) && !empty($dados['id']) && $dados['id'] > 0) {
                        $idsCriados[$id] = $this->copyStart($info, $struct, $dados);
                    }
                }
            }

            return $idsCriados;

        } elseif (is_int($ids)) {
            $dados = $entity->getDataEntity($ids);
            if (isset($dados['id']) && !empty($dados['id']) && $dados['id'] > 0) {
                return $this->copyStart($info, $struct, $dados);
            }
        }

        return null;
    }

    private function copyStart($info, $struct, $dados)
    {
        $dados = $this->extendCopy($dados, $struct, $info['extend'] ?? null);
        $dados = $this->extendMultCopy($dados, $struct, $info['extend_mult'] ?? null);

        return $this->entityCopy($dados, $info, $struct);
    }

    private function extendCopy($dados, $struct, $extend = null)
    {
        if ($extend) {
            foreach ($extend as $column) {
                $entidade = new Entity($struct[$column]['table']);
                $dados[$column] = $this->filterDadosCopy($dados[$column], $entidade->getJsonInfoEntity(), $entidade->getJsonStructEntity());
            }
        }

        return $dados;
    }

    private function entityCopy($dados, $info, $struct)
    {
        $dados = $this->filterDadosCopy($dados, $info, $struct);

        $entity = new Entity($this->entity);
        return $entity->insertDataEntity($dados);
    }

    private function filterDadosCopy($dados, $info, $struct)
    {
        if (isset($dados[$info['primary']])) {
            unset($dados[$info['primary']]);
        }

        //campos unicos modifica info
        if (!empty($info['unique'])) {
            foreach ($info['unique'] as $column) {
                if (empty($struct[$column]['table'])) {
                    if (is_string($dados[$column])) {
                        $dados[$column] = preg_match('/-cp--\d{1,4}/i', $dados[$column]) ? explode('-cp--', $dados[$column])[0] : $dados[$column];
                        $dados[$column] .= "-cp--" . rand(1, 1000) . date("s") . date("i");
                    } elseif (is_numeric($dados[$column])) {
                        $dados[$column] = (rand(0, 10000)) * -1;
                    }
                }
            }
        }

        return $dados;
    }

    private function extendMultCopy($dados, $struct, $extend = null)
    {
        if ($extend) {
            foreach ($extend as $column) {
                $entidade = new Entity($struct[$column]['table']);
                $dataExtend = $dados[$column];
                unset($dados[$column]);
                if(!empty($dataExtend) && is_array($dataExtend)) {
                    foreach ($dataExtend as $dado) {
                        $dados[$column][] = $this->filterDadosCopy($dado, $entidade->getJsonInfoEntity(), $entidade->getJsonStructEntity());
                    }
                }
            }
        }

        return $dados;
    }
}