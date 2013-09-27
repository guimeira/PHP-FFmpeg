<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <dev.team@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FFMpeg\Filters\Video;

use FFMpeg\Format\VideoInterface;
use FFMpeg\Media\Video;
use FFMpeg\Coordinate\Point;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Exception\InvalidArgumentException;

class WatermarkFilter implements VideoFilterInterface
{
    /** @var string */
    private $watermarkImage;
    /** @var Point */
    private $position;
    /** @var Dimension */
    private $size;

    public function __construct($watermarkImage, Point $position = null, Dimension $size = null, $priority = 1)
    {
        if (!file_exists($watermarkImage)) {
            throw new InvalidArgumentException(sprintf('File %s does not exist', $watermarkImage));
        }

        $this->watermarkImage = $watermarkImage;
        $this->position = $position;
        $this->size = $size;
        $this->priority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getWatermarkImage()
    {
        return $this->watermarkImage;
    }

    /**
     * @return Point
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return Dimension
     */
    public function getSize()
    {
        return $this->size;
    }

    public function apply(Video $video, VideoInterface $format)
    {
        $commands = array('-vf');

        $imageStr = 'movie=' . $this->watermarkImage;

        if ($this->size !== null) {
            $imageStr .= ', scale=' . $this->size->getWidth() . ':' . $this->size->getHeight();
        }

        $imageStr .= ' [watermark]; ';

        $overlayStr = '[in][watermark] overlay=';

        if ($this->position !== null) {
            $overlayStr .= $this->position->getX() . ':' . $this->position->getY();
        } else {
            $overlayStr .= '0:0';
        }

        $overlayStr .= ' [out]';

        $commands[] = $imageStr . $overlayStr;

        return $commands;
    }
}
