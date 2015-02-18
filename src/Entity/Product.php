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
 * @ORM\Table(name="productreview_product")
 */
class Product extends \WebCMS\Entity\Entity
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
     * @orm\Column(type="text")
     */
    private $specification;

    /**
     * @ORM\Column(type="boolean")
     */
    private $homepage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hide;

    /**
     * @orm\OneToMany(targetEntity="Photo", mappedBy="product") 
     * @var Array
     */
    private $photos;

    /**
     * @orm\OneToMany(targetEntity="Review", mappedBy="product") 
     * @var Array
     */
    private $reviews;

    /**
     * @orm\OneToMany(targetEntity="Download", mappedBy="product") 
     * @var Array
     */
    private $downloads;

    /**
     * @orm\Column(type="text")
     */
    private $accessories;


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

    public function getSpecification()
    {
        return $this->specification;
    }
    
    public function setSpecification($specification)
    {
        $this->specification = $specification;
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

    public function getReviews()
    {
        return $this->reviews;
    }
    
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
        return $this;
    }

    public function getDefaultReview(){
        foreach($this->reviews() as $review){
            if($review->getMain()){
                return $review;
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

    public function getAccessories()
    {
        return $this->accessories;
    }
    
    public function setAccessories($accessories)
    {
        $this->accessories = $accessories;
        return $this;
    }

    public function getDownloads()
    {
        return $this->downloads;
    }
    
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
        return $this;
    }
}
