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
 * @ORM\Table(name="productreview_download")
 */
class Download extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hide;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $main;

    /**
     * @ORM\ManyToOne(targetEntity="Downloadcategory", inversedBy="downloads") 
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Array
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="downloads") 
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Array
     */
    private $product;

    /**
     * @orm\OneToMany(targetEntity="Downloadfile", mappedBy="download") 
     * @var Array
     */
    private $files;

    /**
     * @orm\ManyToOne(targetEntity="WebCMS\Entity\Page")
     * @orm\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $page;


    public function __construct()
    {
        $this->hide = false;
        $this->main = false;
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

    public function getHide()
    {
        return $this->hide;
    }
    
    public function setHide($hide)
    {
        $this->hide = $hide;
        return $this;
    }

    public function getMain()
    {
        return $this->main;
    }
    
    public function setMain($main)
    {
        $this->main = $main;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }
    
    public function setCategory($category)
    {
        $this->category = $category;
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

    public function getFiles()
    {
        return $this->files;
    }
    
    public function setFiles($files)
    {
        $this->files = $files;
        return $this;
    }

    public function getDefaultFile(){
        foreach($this->getFiles() as $file){
            if($file->getMain()){
                return $file;
            }
        }
        
        return NULL;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }
    
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }


}
