<?php

namespace Dealt\DealtSDK\GraphQL\Types\Object;

use Dealt\DealtSDK\GraphQL\GraphQLObjectInterface;

abstract class AbstractObjectType implements GraphQLObjectInterface
{
    /** @var string */
    public static $objectName;

    /** @var array<string, mixed> */
    public static $objectDefinition;

    public static function toFragment(): string
    {
        $definitions = static::$objectDefinition;
        $fragments   = [];

        foreach ($definitions as $key => $definition) {
            if (is_array($definition) && (!isset($definition['isEnum']) || $definition['isEnum'] !== true)) {
                /** @var AbstractObjectType|string */
                $subType = $definition['objectClass'];
                array_push($fragments, "$key { {$subType::toFragment()} }");
                continue;
            }

            array_push($fragments, $key);
        }

        return join(' ', $fragments);
    }

    public function setProperty($key, $value): GraphQLObjectInterface
    {
        $definitions = array_keys(static::$objectDefinition);
        if (in_array($key, $definitions)) {
            $this->$key = $value;
        }

        return $this;
    }

    public static function fromJson($json): GraphQLObjectInterface
    {
        $objectClass = static::class;
        $definitions = static::$objectDefinition;

        $class      = new $objectClass();

        foreach ($definitions as $key => $definition) {
            if (!isset($json->$key)) {
                continue;
            }

            if (is_array($definition)) {
                $subObjectClass = $definition['objectClass'];

                if (isset($definition['isEnum']) && $definition['isEnum'] === true) {
                    $class->setProperty($key, constant("{$subObjectClass}::{$json->$key}"));
                    continue;
                }

                /** @var AbstractObjectType */
                $subClass = new $subObjectClass();
                $class->setProperty($key, $subClass->fromJson($json->$key));
            } else {
                $class->setProperty($key, $json->$key);
            }
        }

        return $class;
    }
}