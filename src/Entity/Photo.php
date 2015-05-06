<?php

/**
 * This file is part of the ProductReview module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\ProductreviewModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="productreview_photo")
 */
class Photo extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="photos") 
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\Column(type="boolean")
     */
    private $main;

    /**
     * @ORM\Column(type="boolean")
     */
    private $thumbnail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $inCarousel;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;


    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of path.
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the value of path.
     *
     * @param mixed $path the path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }
    
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Gets the value of main.
     *
     * @return mixed
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Sets the value of main.
     *
     * @param mixed $main the main
     *
     * @return self
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Gets the value of created.
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the value of created.
     *
     * @param mixed $created the created
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }
    
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    public function getInCarousel()
    {
        return $this->inCarousel;
    }
    
    public function setInCarousel($inCarousel)
    {
        $this->inCarousel = $inCarousel;
        return $this;
    }
}
