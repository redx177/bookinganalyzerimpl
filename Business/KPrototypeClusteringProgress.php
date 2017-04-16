<?php

class KPrototypeClusteringProgress extends ClusteringProgress
{
    function getClusteringConfig(ConfigProvider $config): array {
        return $config->get('kprototype');
    }
}