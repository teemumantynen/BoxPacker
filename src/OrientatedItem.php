<?php
/**
 * Box packing (3D bin packing, knapsack problem).
 *
 * @author Doug Wright
 */
declare(strict_types=1);

namespace DVDoug\BoxPacker;

use JsonSerializable;
use function min;

/**
 * An item to be packed.
 *
 * @author Doug Wright
 */
class OrientatedItem implements JsonSerializable
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var float[]
     */
    protected static $tippingPointCache = [];

    /**
     * Constructor.
     *
     * @param Item $item
     * @param int  $width
     * @param int  $length
     * @param int  $depth
     */
    public function __construct(Item $item, int $width, int $length, int $depth)
    {
        $this->item = $item;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
    }

    /**
     * Item.
     *
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * Item width in mm in it's packed orientation.
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Item length in mm in it's packed orientation.
     *
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Item depth in mm in it's packed orientation.
     *
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * Calculate the surface footprint of the current orientation.
     *
     * @return int
     */
    public function getSurfaceFootprint(): int
    {
        return $this->width * $this->length;
    }

    /**
     * @return float
     */
    public function getTippingPoint(): float
    {
        $cacheKey = $this->width . '|' . $this->length . '|' . $this->depth;

        if (isset(static::$tippingPointCache[$cacheKey])) {
            $tippingPoint = static::$tippingPointCache[$cacheKey];
        } else {
            $tippingPoint = atan(min($this->length, $this->width) / ($this->depth ?: 1));
            static::$tippingPointCache[$cacheKey] = $tippingPoint;
        }

        return $tippingPoint;
    }

    /**
     * Is this item stable (low centre of gravity), calculated as if the tipping point is >15 degrees.
     *
     * N.B. Assumes equal weight distribution.
     *
     * @return bool
     */
    public function isStable(): bool
    {
        return $this->getTippingPoint() > 0.261;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'item' => $this->item,
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->width . '|' . $this->length . '|' . $this->depth;
    }
}
