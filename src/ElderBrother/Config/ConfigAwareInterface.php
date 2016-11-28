<?php

namespace uuf6429\ElderBrother\Config;

interface ConfigAwareInterface
{
    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config);
}
