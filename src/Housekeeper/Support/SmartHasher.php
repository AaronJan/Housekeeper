<?php

namespace Housekeeper\Support;

/**
 * Class SmartHasher
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Support
 */
class SmartHasher
{
    /**
     * Hash anything.
     *
     * @param mixed $object
     * @return string
     */
    static public function hash($object)
    {
        $object = is_array($object) ? $object : [$object];

        array_walk_recursive($object, function ($item) {
            if ($item instanceof \Closure) {
                $reflection = new \ReflectionFunction($item);

                // Unique and fast.
                $item = serialize($reflection->getClosureScopeClass()) .
                    $reflection->getNumberOfParameters() .
                    $reflection->getNamespaceName() .
                    $reflection->getStartLine() .
                    $reflection->getEndLine();
            }
        });

        return md5(serialize($object));
    }
}