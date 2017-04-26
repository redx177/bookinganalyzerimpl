<?php

class DBScanClusteringProgress extends ClusteringProgress
{
    function getClusteringConfig(ConfigProvider $config): array {
        return $config->get('dbscan');
    }
}