<?php

/**
 * This file is part of the Product Review Module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\ProductreviewModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="productreview_review")
 */
class Review extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

    /**
     * @gedmo\Slug(fields={"name"})
     * @orm\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @orm\Column(type="text")
     */
    private $text;

    /**
     * @orm\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @orm\OneToMany(targetEntity="PhotoReview", mappedBy="review") 
     * @var Array
     */
    private $photos;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="reviews") 
     */
    private $product;

    /**
     * @ORM\Column(type="boolean")
     */
    private $homepage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hide;


    public function __construct()
    {
        $this->hide = false;
        $this->homepage = false;
    }


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
     * Gets the value of slug.
     *
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function getText()
    {
        return $this->text;
    }
    
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }
    
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }
    
    public function setPrice($price)
    {
        $this->price = $price;
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

    public function getPhotos() {
        return $this->photos;
    }

    public function setPhotos(Array $photos) {
        $this->photos = $photos;
    }
    
    public function getDefaultPhoto(){
        foreach($this->getPhotos() as $photo){
            if($photo->getMain()){
                return $photo;
            }
        }
        
        return NULL;
    }

    public function getHide()
    {
        return $this->hide;
    }
    
    public function setHide($hide)
    {
        $this->hide = $hide;
        return $this;
    }

    public function getHomepage()
    {
        return $this->homepage;
    }
    
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
        return $this;
    }
}