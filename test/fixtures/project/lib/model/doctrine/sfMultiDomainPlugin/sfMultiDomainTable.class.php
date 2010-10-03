<?php


class sfMultiDomainTable extends PluginsfMultiDomainTable
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfMultiDomain');
    }
}