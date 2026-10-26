<?php

declare(strict_types=1);

namespace MSSTC4PHP\DtoCollection\Doctrine;

use MSSTC4PHP\DtoCollection\Behaviour\MutableListBehaviour;

/**
 * Everything the ODM variant needs: a mutable list that is also Doctrine's Collection.
 *
 * Where both define a method, Doctrine's signature wins; the behaviour is the same.
 *
 * @template T
 */
trait OdmListBehaviour
{
    /**
     * @use MutableListBehaviour<T>
     * @use DoctrineCollectionBehaviour<T>
     */
    use MutableListBehaviour, DoctrineCollectionBehaviour {
        DoctrineCollectionBehaviour::count insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::isEmpty insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::toArray insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::getIterator insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::offsetExists insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::offsetGet insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::offsetSet insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::offsetUnset insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::filter insteadof MutableListBehaviour;
        DoctrineCollectionBehaviour::reduce insteadof MutableListBehaviour;
    }
}
