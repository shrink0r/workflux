<?php

namespace Workflux;

interface ParamBagInterface
{
    /**
     * @param string $param_name
     *
     * @return mixed
     */
    public function get(string $param_name);

    /**
     * @param string $param_name
     *
     * @return bool
     */
    public function has(string $param_name): bool;

    /**
     * @param string $param_name
     * @param mixed $param_value
     * @param bool $treat_name_as_path
     *
     * @return ParamBagInterface
     */
    public function withParam(string $param_name, $param_value, bool $treat_name_as_path = true): ParamBagInterface;

    /**
     * @param mixed[] $params
     *
     * @return ParamBagInterface
     */
    public function withParams(array $params): ParamBagInterface;

    /**
     * @param string $param_name
     *
     * @return ParamBagInterface
     */
    public function withoutParam(string $param_name): ParamBagInterface;

    /**
     * @param string[] $param_names
     *
     * @return ParamBagInterface
     */
    public function withoutParams(array $param_names): ParamBagInterface;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
