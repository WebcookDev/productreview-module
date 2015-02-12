<?php

/**
 * This file is part of the Product review module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\ProductReviewModule;

/**
 * Description of Product Review
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class ProductReview extends \WebCMS\Module
{
	/**
	 * [$name description]
	 * @var string
	 */
    protected $name = 'ProductReview';
    
    /**
     * [$author description]
     * @var string
     */
    protected $author = 'Jakub Sanda';
    
    protected $searchable = true;

    /**
     * [$presenters description]
     * @var array
     */
    protected $presenters = array(
		array(
		    'name' => 'Products',
		    'frontend' => true,
		    'parameters' => true
		),
        array(
            'name' => 'Reviews',
            'frontend' => true,
            'parameters' => true
        ),
        array(
            'name' => 'Downloads',
            'frontend' => true,
            'parameters' => true
        ),
		array(
		    'name' => 'Settings',
		    'frontend' => false
		)
    );

    public function __construct()
    {
        
    }

    public function search(\Doctrine\ORM\EntityManager $em, $phrase, \WebCMS\Entity\Language $language)
    {
        
    }
}