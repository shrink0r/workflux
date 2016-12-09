<?php

namespace Workflux\Param;

interface ParamHolderInterface
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
     * @return self
     */
    public function withParam(string $param_name, $param_value, bool $treat_name_as_path = true): self;

    /**
     * @param mixed[] $params
     *
     * @return self
     */
    public function withParams(array $params): self;

    /**
     * @param string $param_name
     *
     * @return ParamHolderInterface
     */
    public function withoutParam(string $param_name): ParamHolderInterface;

    /**
     * @param string[] $param_names
     *
     * @return ParamHolderInterface
     */
    public function withoutParams(array $param_names): ParamHolderInterface;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
