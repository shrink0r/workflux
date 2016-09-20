<?php

namespace Workflux;

interface ParamBagInterface
{
    /**
     * @param string $param_name
     *
     * @return mixed
     */
    public function get($param_name);

    /**
     * @param string $param_name
     *
     * @return bool
     */
    public function has($param_name);

    /**
     * @param string $param_name
     * @param mixed $param_value
     * @param bool $treat_name_as_path
     *
     * @return ParamBagInterface
     */
    public function withParam($param_name, $param_value, $treat_name_as_path = true);

    /**
     * @param mixed[] $params
     *
     * @return ParamBagInterface
     */
    public function withParams(array $params);

    /**
     * @param string $param_name
     *
     * @return ParamBagInterface
     */
    public function withoutParam($param_name);

    /**
     * @param string[] $param_names
     *
     * @return ParamBagInterface
     */
    public function withoutParams(array $param_names);

    /**
     * @return mixed[]
     */
    public function toArray();
}
