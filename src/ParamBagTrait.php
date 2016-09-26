<?php

namespace Workflux;

trait ParamBagTrait
{
    /**
     * @param mixed[] $params
     */
    private $params = [];

    /**
     * @param string $param_name
     * @param bool $treat_name_as_path
     *
     * @return mixed
     */
    public function get(string $param_name, bool $treat_name_as_path = true)
    {
        if (!$treat_name_as_path) {
            return $this->has($param_name) ? $this->params[$param_name] : null;
        }
        $params = $this->params;
        $name_parts = array_reverse(explode('.', $param_name));
        $cur_val = &$params;
        while (count($name_parts) > 1 && $cur_name = array_pop($name_parts)) {
            if (!array_key_exists($cur_name, $cur_val)) {
                return null;
            }
            $cur_val = &$cur_val[$cur_name];
        }

        return array_key_exists($name_parts[0], $cur_val) ? $cur_val[$name_parts[0]] : null;
    }

    /**
     * @param string $param_name
     *
     * @return bool
     */
    public function has(string $param_name): bool
    {
        return array_key_exists($param_name, $this->params);
    }

    /**
     * @param string $param_name
     * @param mixed $param_value
     * @param bool $treat_name_as_path
     *
     * @return self
     */
    public function withParam(string $param_name, $param_value, bool $treat_name_as_path = true): ParamBagInterface
    {
        $param_bag = clone $this;
        if ($treat_name_as_path) {
            $name_parts = array_reverse(explode('.', $param_name));
            $cur_val = &$param_bag->params;
            while (count($name_parts) > 1 && $cur_name = array_pop($name_parts)) {
                if (!isset($cur_val[$cur_name])) {
                    $cur_val[$cur_name] = [];
                }
                $cur_val = &$cur_val[$cur_name];
            }
            $cur_val[$name_parts[0]] = $param_value;

            return $param_bag;
        }
        $param_bag->params[$param_name] = $param_value;

        return $param_bag;
    }

    /**
     * @param mixed[] $params
     *
     * @return self
     */
    public function withParams(array $params): ParamBagInterface
    {
        $param_bag = clone $this;
        $param_bag->params = array_merge($param_bag->params, $params);

        return $param_bag;
    }

    /**
     * @param string $param_name
     *
     * @return self
     */
    public function withoutParam(string $param_name): ParamBagInterface
    {
        if (!$this->has($param_name)) {
            return $this;
        }
        $param_bag = clone $this;
        unset($param_bag->params[$param_name]);

        return $param_bag;
    }

    /**
     * @param string[] $param_names
     *
     * @return self
     */
    public function withoutParams(array $param_names): ParamBagInterface
    {
        return array_reduce(
            $param_names,
            function (ParamBagInterface $param_bag, $param_name) {
                return $param_bag->withoutParam($param_name);
            },
            $this
        );
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->params;
    }
}
