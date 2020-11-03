<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//require_once AMWSCPF_PATH.'/core/classes/amazon.php';
class AMWSCPF_Categories extends WP_List_Table
{
    public $categories = "";
    public $site = "";
    public $code = "";
    public $importedTemplates = array();
    public $datatablesdata;
    public $countryfullname;


    function __construct()
    {

        global $status, $page;
        $this->userPreferences();
        //Set parent defaults
        parent::__construct(array(
            'singular' => 'template',     //singular name of the listed records
            'plural' => 'templates',    //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    function get_list()
    {
        $file_index = array(

            // amazon.com (US)
            'US' => array(
                'site' => 'amazon.com',
                'code' => 'US',
                'categories' => array(
                    //AutoAccessry
                    'AutoAccessry' => [
                        'tmpl_id'   => 1,
                        'title' => 'AutoAccessry',
                        'meta_name' => 'AutoAccessry',
                        'templates' => array(
                            'Flat.File.AutoAccessory-Template.csv',
                            'Flat.File.AutoAccessory-DataDefinitions.csv',
                            'Flat.File.AutoAccessory-ValidValues.csv',
                            // 'Flat.File.TiresAndWheels-Template.csv',
                            // 'Flat.File.TiresAndWheels-DataDefinitions.csv',
                            // 'Flat.File.TiresAndWheels-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'automotive_browse_tree_guide.csv',
                        ),
                    ],

                    // Baby
                    'Baby' => array(
                        'tmpl_id' => 2,
                        'title' => 'Baby',
                        'templates' => array(
                            'Flat.File.Baby-Template.csv',
                            'Flat.File.Baby-DataDefinitions.csv',
                            'Flat.File.Baby-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'baby-products_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Baby'
                    ),

                    // Beauty
                    'Beauty' => array(
                        'tmpl_id'  => 3,
                        'title' => 'Beauty',
                        'templates' => array(
                            'Flat.File.Beauty-Template.csv',
                            'Flat.File.Beauty-DataDefinitions.csv',
                            'Flat.File.Beauty-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'beauty_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Beauty'
                    ),
                    //Books
                    'BookLoader' => array(
                        'tmpl_id'  => 4,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader-Template.csv',
                            'Flat.File.BookLoader-DataDefinitions.csv',
                            'Flat.File.BookLoader-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    // Camera & Photo
                    'CameraAndPhoto' => array(
                        'tmpl_id'    => 5,
                        'title' => 'Camera & Photo',
                        'templates' => array(
                            'Flat.File.CameraAndPhoto-Template.csv',
                            'Flat.File.CameraAndPhoto-DataDefinitions.csv',
                            'Flat.File.CameraAndPhoto-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'electronics_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'CameraAndPhoto'
                    ),

                    // Clothing
                    'Clothing' => [
                        'tmpl_id'    =>  6,
                        'title' => 'Clothing',
                        'meta_name' => 'Clothing',
                        'templates' => [
                            'Flat.File.Clothing-Template.csv',
                            'Flat.File.Clothing-DataDefinitions.csv',
                            'Flat.File.Clothing-ValidValues.csv'
                        ],
                        'btguides' => ['apparel_browse_tree_guide.csv'],
                    ],
                    'Computers' => [
                        'tmpl_id'    => 7,
                        'title' => 'Computers',
                        'meta_name' => 'Computers',
                        'templates' => array(
                            'Flat.File.Computers-Template.csv',
                            'Flat.File.Computers-DataDefinitions.csv',
                            'Flat.File.Computers-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'electronics_browse_tree_guide.csv',
                        ),
                    ],
                    'ConsumerElectronics' => [
                        'tmpl_id'    => 8,
                        'title' => 'Consumer Electronics',
                        'meta_name' => 'ConsumerElectronics',
                        'templates' => array(
                            'Flat.File.ConsumerElectronics-Template.csv',
                            'Flat.File.ConsumerElectronics-DataDefinitions.csv',
                            'Flat.File.ConsumerElectronics-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'electronics_browse_tree_guide.csv',
                        ),
                    ],
                    'EntertainmentCollectibles' => [
                        'tmpl_id'    =>  9,
                        'title' => 'Entertainment Collectibles',
                        'meta_name' => 'EntertainmentCollectibles',
                        'btguides' => ['entertainment-collectibles_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.EntertainmentCollectibles-Template.csv',
                            'Flat.File.EntertainmentCollectibles-DataDefinitions.csv',
                            'Flat.File.EntertainmentCollectibles-ValidValues.csv'
                        ]
                    ],
                    'FoodAndBeverages' => [
                        'tmpl_id'    => 10,
                        'title' => 'FoodAndBeverages',
                        'meta_name' => 'FoodAndBeverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages-Template.csv',
                            'Flat.File.FoodAndBeverages-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'grocery_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodServiceAndJanSan' => [
                        'tmpl_id'    => 11,
                        'title' => 'Food Service & Jan San',
                        'meta_name' => 'FoodServiceAndJanSan',
                        'btguides' => [''],
                        'templates' => [
                            'Flat.File.FoodServiceAndJanSan-Template.csv',
                            'Flat.File.FoodServiceAndJanSan-DataDefinitions.csv',
                            'Flat.File.FoodServiceAndJanSan-ValidValues.csv'
                        ]
                    ],
                    'GiftCards' => [
                        'tmpl_id'    => 12,
                        'title' => 'Gift Cards',
                        'meta_name' => 'GiftCards',
                        'btguides' => '',
                        'templates' => [
                            'Flat.File.GiftCards-Template.csv',
                            'Flat.File.GiftCards-DataDefinitions.csv',
                            'Flat.File.GiftCards-ValidValues.csv'
                        ]
                    ],
                    'Health' => [
                        'tmpl_id'    => 13,
                        'title' => 'Health',
                        'meta_name' => 'Health',
                        'templates' => array(
                            'Flat.File.Health-Template.csv',
                            'Flat.File.Health-DataDefinitions.csv',
                            'Flat.File.Health-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'health_browse_tree_guide.csv',
                        ),
                    ],
                    'HomeImprovement' => [
                        'tmpl_id'    => 14,
                        'title' => 'Home Improvement',
                        'meta_name' => 'HomeImprovement',
                        'templates' => array(
                            'Flat.File.HomeImprovement-Template.csv',
                            'Flat.File.HomeImprovement-DataDefinitions.csv',
                            'Flat.File.HomeImprovement-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'home-kitchen_browse_tree_guide.csv',
                            'garden_browse_tree_guide.csv',
                            'arts-and-crafts_browse_tree_guide.csv',
                        ),
                    ],
                    'Home'=> [
                        'tmpl_id'    => 15,
                        'title' => 'Home',
                        'meta_name' => 'Home',
                        'templates' => array(
                            'Flat.File.Home-Template.csv',
                            'Flat.File.Home-DataDefinitions.csv',
                            'Flat.File.Home-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'home-kitchen_browse_tree_guide.csv',
                            'garden_browse_tree_guide.csv',
                            'arts-and-crafts_browse_tree_guide.csv',
                        ),
                    ],
                    'Industrial' => [
                        'tmpl_id'    => 16,
                        'title' => 'Industrial',
                        'meta_name' => 'Industrial',
                        'templates' => array(
                            'Flat.File.Industrial-Template.csv',
                            'Flat.File.Industrial-DataDefinitions.csv',
                            'Flat.File.Industrial-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'industrial_browse_tree_guide.csv',
                        ),
                    ],

                    'Jewelry' => [
                        'tmpl_id'    => 18,
                        'title' => 'Jewelry',
                        'meta_name' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry-Template.csv',
                            'Flat.File.Jewelry-DataDefinitions.csv',
                            'Flat.File.Jewelry-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'jewelry_browse_tree_guide.csv',
                        ),
                    ],

                    'LabSupplies' => [
                        'tmpl_id'    => 22,
                        'title' => 'Lab Supplies',
                        'meta_name' => 'LabSupplies',
                        'templates' => array(
                            'Flat.File.LabSupplies-Template.csv',
                            'Flat.File.LabSupplies-DataDefinitions.csv',
                            'Flat.File.LabSupplies-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'industrial_browse_tree_guide.csv',
                        ),
                    ],

                    'Listingloader' => [
                        'tmpl_id'    => 17,
                        'title' => 'Listing Loader',
                        'meta_name' => 'Offer',
                        'btguides' => '',
                        'templates' => ['Flat.File.Listingloader-Template.csv']
                    ],
                    
                    'MechanicalFasteners' => [
                        'tmpl_id'    => 19,
                        'title' => 'Mechanical Fasteners',
                        'meta_name' => 'MechanicalFasteners',
                        'templates' => array(
                            'Flat.File.MechanicalFasteners-Template.csv',
                            'Flat.File.MechanicalFasteners-DataDefinitions.csv',
                            'Flat.File.MechanicalFasteners-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'industrial_browse_tree_guide.csv',
                        ),
                    ],
                    'Music' => [
                        'tmpl_id'    => 20,
                        'title' => 'Music',
                        'meta_name' => 'Music',
                        'templates' => array(
                            'Flat.File.Music-Template.csv',
                            'Flat.File.Music-DataDefinitions.csv',
                            'Flat.File.Music-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'MusicalInstruments' => [
                        'tmpl_id'    => 21,
                        'title' => 'Musical Instruments',
                        'meta_name' => 'MusicalInstruments',
                        'templates' => array(
                            'Flat.File.MusicalInstruments-Template.csv',
                            'Flat.File.MusicalInstruments-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    
                    'Office' => [
                        'tmpl_id'    => 23,
                        'title' => 'Office',
                        'meta_name' => 'Office',
                        'templates' => array(
                            'Flat.File.Office-Template.csv',
                            'Flat.File.Office-DataDefinitions.csv',
                            'Flat.File.Office-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'office-products_browse_tree_guide.csv',
                        ),
                    ],
                    'PetSupplies' => [
                        'tmpl_id'    => 24,
                        'title' => 'Pet Supplies',
                        'meta_name' => 'PetSupplies',
                        'templates' => array(
                            'Flat.File.PetSupplies-Template.csv',
                            'Flat.File.PetSupplies-DataDefinitions.csv',
                            'Flat.File.PetSupplies-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'pet-supplies_browse_tree_guide.csv',
                        ),
                    ],
                    'PowerTransmission' => [
                        'tmpl_id'    => 25,
                        'title' => 'Power Transmission',
                        'meta_name' => 'PowerTransmission',
                        'templates' => array(
                            'Flat.File.PowerTransmission-Template.csv',
                            'Flat.File.PowerTransmission-DataDefinitions.csv',
                            'Flat.File.PowerTransmission-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'industrial_browse_tree_guide.csv',
                        ),
                    ],
                    'RawMaterials' => [
                        'tmpl_id'    => 26,
                        'title' => 'Raw Materials',
                        'meta_name' => 'RawMaterials',
                        'templates' => array(
                            'Flat.File.RawMaterials-Template.csv',
                            'Flat.File.RawMaterials-DataDefinitions.csv',
                            'Flat.File.RawMaterials-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'industrial_browse_tree_guide.csv',
                        ),
                    ],
                    'SWVG' => [
                        'tmpl_id'    => 27,
                        'title' => 'Video Games',
                        'meta_name' => 'SWVG',
                        'templates' => array(
                            'Flat.File.SWVG-Template.csv',
                            'Flat.File.SWVG-DataDefinitions.csv',
                            'Flat.File.SWVG-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'software_browse_tree_guide.csv',
                            'videogames_browse_tree_guide.csv',
                        ),
                    ],
                    'Shoes' => [
                        'tmpl_id'    => 28,
                        'title' => 'Shoes',
                        'meta_name' => 'Shoes',
                        'templates' => array(
                            'Flat.File.Shoes-Template.csv',
                            'Flat.File.Shoes-DataDefinitions.csv',
                            'Flat.File.Shoes-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'shoes_browse_tree_guide.csv',
                        ),
                    ],
                    'SportsMemorabilia' => [
                        'tmpl_id'    => 29,
                        'title' => 'Sports Memorabilia',
                        'meta_name' => 'SportsMemorabilia',
                        'templates' => array(
                            'Flat.File.SportsMemorabilia-Template.csv',
                            'Flat.File.SportsMemorabilia-DataDefinitions.csv',
                            'Flat.File.SportsMemorabilia-ValidValues.csv',
                        ),
                        'btguides' => array(),
                    ],
                    'Sports' => [
                        'tmpl_id'    => 30,
                        'title' => 'Sports',
                        'meta_name' => 'Sports',
                        'templates' => array(
                            'Flat.File.Sports-Template.csv',
                            'Flat.File.Sports-DataDefinitions.csv',
                            'Flat.File.Sports-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_sports_browse_tree_guide.csv',
                        ),
                    ],
                    'Toys' => [
                        'tmpl_id'    => 31,
                        'title' => 'Toys',
                        'meta_name' => 'Toys',
                        'templates' => array(
                            'Flat.File.Toys-Template.csv',
                            'Flat.File.Toys-DataDefinitions.csv',
                            'Flat.File.Toys-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'toys-and-games_browse_tree_guide.csv',
                        ),
                    ],
                    'Video' => [
                        'tmpl_id'    => 32,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video-Template.csv',
                            'Flat.File.Video-DataDefinitions.csv',
                            'Flat.File.Video-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Watches' => [
                        'tmpl_id'    => 33,
                        'title' => 'Watches',
                        'meta_name' => 'Watches',
                        'templates' => array(
                            'Flat.File.Watches-Template.csv',
                            'Flat.File.Watches-DataDefinitions.csv',
                            'Flat.File.Watches-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'watches_browse_tree_guide.csv',
                        ),
                    ],
                    // 'Wireless' => [
                    // 'tmpl_id'   => 34,
                    //     'title' => 'Wireless',
                    //     'meta_name' => 'Wireless',
                    //     'templates' => array(
                    //         'Flat.File.Wireless-Template.csv',
                    //         'Flat.File.Wireless-DataDefinitions.csv',
                    //         'Flat.File.Wireless-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'cellphone-accessories_browse_tree_guide.csv',
                    //     ),
                    // ],


                ),
            ),

            // amazon.co.uk (UK)
            'UK' => array(
                'site' => 'amazon.co.uk',
                'code' => 'UK',
                'categories' => array(
                    'AutoAccessory' => [
                        'tmpl_id'        => 35,
                        'meta_name' => 'AutoAccessory',
                        'title' => 'Auto Accessory',
                        'templates' => array(
                            'Flat.File.AutoAccessory.uk-Template.csv',
                            'Flat.File.AutoAccessory.uk-DataDefinitions.csv',
                            'Flat.File.AutoAccessory.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_automotive_browse_tree_guide.csv',
                        ),
                    ],
                    'Apparel' => [
                        'tmpl_id'        => 0,
                        'meta_name' => 'Apparel',
                        'title' => 'Apparel',
                        'templates' => array(
                            'Flat.File.Apparel.uk-Template.csv',
                            'Flat.File.Apparel.uk-DataDefinitions.csv',
                            'Flat.File.Apparel.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                            // 'uk_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'AutomaticPricing' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'AutomaticPricing',
                        'title' => 'AutomaticPricing',
                        'templates' => array(
                            'Flat.File.AutomaticPricing.uk-Template.csv',
                            'Flat.File.AutomaticPricing.uk-DataDefinitions.csv',
                            'Flat.File.AutomaticPricing.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                            // 'uk_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'Baby' => [
                        'tmpl_id'    => 36, 
                        'meta_name' => 'Baby',
                        'title' => 'Baby',
                        'templates' => array(
                            'Flat.File.Baby.uk-Template.csv',
                            'Flat.File.Baby.uk-DataDefinitions.csv',
                            'Flat.File.Baby.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_baby-products_browse_tree_guide.csv',
                        ),
                    ],
                    'Beauty' => [
                        'tmpl_id'    => 37,
                        'meta_name' => 'Beauty',
                        'title' => 'Beauty',
                        'templates' => array(
                            'Flat.File.Beauty.uk-Template.csv',
                            'Flat.File.Beauty.uk-DataDefinitions.csv',
                            'Flat.File.Beauty.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_beauty_browse_tree_guide.csv',
                        ),
                    ],
                    'Books' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Books',
                        'templates' => array(
                            'Flat.File.Books.uk-Template.csv',
                            'Flat.File.Books.uk-DataDefinitions.csv',
                            'Flat.File.Books.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'Books'
                    ),
                    'BookLoader' => array(
                        'tmpl_id'    => 38,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.uk-Template.csv',
                            'Flat.File.BookLoader.uk-DataDefinitions.csv',
                            'Flat.File.BookLoader.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_bookloader_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    'Clothing' => [
                        'tmpl_id'    => 39,
                        'meta_name' => 'Clothing',
                        'title' => 'Clothing',
                        'templates' => array(
                            'Flat.File.Clothing.uk-Template.csv',
                            'Flat.File.Clothing.uk-DataDefinitions.csv',
                            'Flat.File.Clothing.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'Computers' => [
                        'tmpl_id'    => 40,
                        'meta_name' => 'Computers',
                        'title' => 'Computers',
                        
                            'templates' => array(
                                'Flat.File.Computers.uk-Template.csv',
                                'Flat.File.Computers.uk-DataDefinitions.csv',
                                'Flat.File.Computers.uk-ValidValues.csv',
                            ),
                            'btguides' => array('uk_electronics_browse_tree_guide.csv'),
                    ],
                    'CameraPhoto' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'CameraPhoto',
                        'title' => 'CameraPhoto',
                        
                            'templates' => array(
                                'Flat.File.CameraPhoto.uk-Template.csv',
                                'Flat.File.CameraPhoto.uk-DataDefinitions.csv',
                                'Flat.File.CameraPhoto.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    // 'CE' => [
                    //     'meta_name' => 'CE',
                    //     'title' => 'CE',
                        
                    //         'templates' => array(
                    //             'Flat.File.CE.uk-Template.csv',
                    //             'Flat.File.CE.uk-DataDefinitions.csv',
                    //             'Flat.File.CE.uk-ValidValues.csv',
                    //         ),
                    //         'btguides' => array(''),
                    // ],
                    'ConsumerElectronics' => [
                        'tmpl_id'    => 41,
                        'meta_name' => 'ConsumerElectronics',
                        'title' => 'ConsumerElectronics',
                        'templates' => array(
                            'Flat.File.ConsumerElectronics.uk-Template.csv',
                            'Flat.File.ConsumerElectronics.uk-DataDefinitions.csv',
                            'Flat.File.ConsumerElectronics.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_electronics_browse_tree_guide.csv',
                        ),
                    ],
                    'Eyewear' => [
                        'tmpl_id'    => 42,
                        'meta_name' => 'Eyewear',
                        'title' => 'Eyewear',
                        'templates' => array(
                            'Flat.File.Eyewear.uk-Template.csv',
                            'Flat.File.Eyewear.uk-DataDefinitions.csv',
                            'Flat.File.Eyewear.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'EducationalSupplies' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'EducationalSupplies',
                        'title' => 'EducationalSupplies',
                        
                            'templates' => array(
                                'Flat.File.EducationalSupplies.uk-Template.csv',
                                'Flat.File.EducationalSupplies.uk-DataDefinitions.csv',
                                'Flat.File.EducationalSupplies.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'EUCompliance' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'EUCompliance',
                        'title' => 'EUCompliance',
                        
                            'templates' => array(
                                'Flat.File.EUCompliance.uk-Template.csv',
                                'Flat.File.EUCompliance.uk-DataDefinitions.csv',
                                'Flat.File.EUCompliance.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'FoodAndBeverages' => [
                        'tmpl_id'    => 43,
                        'meta_name' => 'FoodAndBeverages',
                        'title' => 'Food & Beverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages.uk-Template.csv',
                            'Flat.File.FoodAndBeverages.uk-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_food_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodServiceAndJanSan' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'FoodServiceAndJanSan',
                        'title' => 'FoodServiceAndJanSan',
                        
                            'templates' => array(
                                'Flat.File.FoodServiceAndJanSan.uk-Template.csv',
                                'Flat.File.FoodServiceAndJanSan.uk-DataDefinitions.csv',
                                'Flat.File.FoodServiceAndJanSan.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'Gourmet' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Gourmet',
                        'title' => 'Gourmet',
                        
                            'templates' => array(
                                'Flat.File.Gourmet.uk-Template.csv',
                                'Flat.File.Gourmet.uk-DataDefinitions.csv',
                                'Flat.File.Gourmet.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'HandheldSoftwareDownloads' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'HandheldSoftwareDownloads',
                        'title' => 'HandheldSoftwareDownloads',
                        
                            'templates' => array(
                                'Flat.File.HandheldSoftwareDownloads.uk-Template.csv',
                                'Flat.File.HandheldSoftwareDownloads.uk-DataDefinitions.csv',
                                'Flat.File.HandheldSoftwareDownloads.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'Home' => [
                        'tmpl_id'    => 44,
                        'meta_name' => 'Home',
                        'title' => 'Home & Garden',
                        'templates' => array(
                            'Flat.File.Home.uk-Template.csv',
                            'Flat.File.Home.uk-DataDefinitions.csv',
                            'Flat.File.Home.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_home-garden_browse_tree_guide.csv',
                        ),
                    ],
                    'Health' => [
                        'tmpl_id'    => 45,
                        'meta_name' => 'Health',
                        'title' => 'Health',
                        'templates' => array(
                            'Flat.File.Health.uk-Template.csv',
                            'Flat.File.Health.uk-DataDefinitions.csv',
                            'Flat.File.Health.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_drugstore_browse_tree_guide.csv',
                        ),
                    ],
                    'HomeImprovement' => [
                        'tmpl_id'    => 46,
                        'meta_name' => 'HomeImprovement',
                        'title' => 'Home Improvement',
                        'templates' => array(
                            'Flat.File.HomeImprovement.uk-Template.csv',
                            'Flat.File.HomeImprovement.uk-DataDefinitions.csv',
                            'Flat.File.HomeImprovement.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_tools_browse_tree_guide.csv',
                        ),
                    ],
                    'Industrial' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Industrial',
                        'title' => 'Industrial',
                        
                            'templates' => array(
                                'Flat.File.Industrial.uk-Template.csv',
                                'Flat.File.Industrial.uk-DataDefinitions.csv',
                                'Flat.File.Industrial.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'Jewelry' => [
                        'tmpl_id'    => 47,
                        'meta_name' => 'Jewelry',
                        'title' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry.uk-Template.csv',
                            'Flat.File.Jewelry.uk-DataDefinitions.csv',
                            'Flat.File.Jewelry.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_jewelry_browse_tree_guide.csv',
                        ),
                    ],
                     'KindleAccessories' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'KindleAccessories',
                        'title' => 'KindleAccessories',
                        
                            'templates' => array(
                                'Flat.File.KindleAccessories.uk-Template.csv',
                                'Flat.File.KindleAccessories.uk-DataDefinitions.csv',
                                'Flat.File.KindleAccessories.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                     'Kitchen' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Kitchen',
                        'title' => 'Kitchen',
                        
                            'templates' => array(
                                'Flat.File.Kitchen.uk-Template.csv',
                                'Flat.File.Kitchen.uk-DataDefinitions.csv',
                                'Flat.File.Kitchen.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                     'LabSupplies' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'LabSupplies',
                        'title' => 'LabSupplies',
                        
                            'templates' => array(
                                'Flat.File.LabSupplies.uk-Template.csv',
                                'Flat.File.LabSupplies.uk-DataDefinitions.csv',
                                'Flat.File.LabSupplies.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'LargeAppliances' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'LargeAppliances',
                        'title' => 'LargeAppliances',
                        
                            'templates' => array(
                                'Flat.File.LargeAppliances.uk-Template.csv',
                                'Flat.File.LargeAppliances.uk-DataDefinitions.csv',
                                'Flat.File.LargeAppliances.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'LawnAndGarden' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'LawnAndGarden',
                        'title' => 'LawnAndGarden',
                        
                            'templates' => array(
                                'Flat.File.LawnAndGarden.uk-Template.csv',
                                'Flat.File.LawnAndGarden.uk-DataDefinitions.csv',
                                'Flat.File.LawnAndGarden.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    'Lighting' => [
                        'tmpl_id'    => 50,
                        'meta_name' => 'Lighting',
                        'title' => 'Lighting',
                        'templates' => array(
                            'Flat.File.Lighting.uk-Template.csv',
                            'Flat.File.Lighting.uk-DataDefinitions.csv',
                            'Flat.File.Lighting.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_lighting_browse_tree_guide.csv',
                        ),
                    ],
                    'ListingLoader' => [
                        'tmpl_id'    => 54,
                        'meta_name' => 'Offer',
                        'title' => 'Listing Loader',
                        'btguides' => '',
                        'templates' => [
                            'ListingLoader-Template.csv',
                            'ListingLoader-DataDefinitions.csv',
                            'ListingLoader-ValidValues.csv'
                        ]
                    ],
                    'Luggage' => [
                        'tmpl_id'    => 51,
                        'meta_name' => 'Luggage',
                        'title' => 'Luggage',
                        'templates' => array(
                            'Flat.File.Luggage.uk-Template.csv',
                            'Flat.File.Luggage.uk-DataDefinitions.csv',
                            'Flat.File.Luggage.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_luggage_browse_tree_guide.csv',
                        ),
                    ],
                    'Music' => [
                        'tmpl_id'    => 53,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        
                            'templates' => array(
                                'Flat.File.Music.uk-Template.csv',
                                'Flat.File.Music.uk-DataDefinitions.csv',
                                'Flat.File.Music.uk-ValidValues.csv',
                            ),
                            'btguides' => array(''),
                    ],
                    // 'MusicalInstruments' => [
                    //     'tmpl_id'    => 52,
                    //     'meta_name' => 'MusicalInstruments',
                    //     'title' => 'MusicalInstruments',
                        
                    //         'templates' => array(
                    //             'Flat.File.MusicalInstruments.uk-Template.csv',
                    //             'Flat.File.MusicalInstruments.uk-DataDefinitions.csv',
                    //             'Flat.File.MusicalInstruments.uk-ValidValues.csv',
                    //         ),
                    //         'btguides' => array(''),
                    // ],

                    'Musical Instrument' => [
                        'tmpl_id'    => 60,
                        'meta_name' => 'Musical Instrument',
                        'title' => 'Musical Instrument',
                        'templates' => array(
                            'Flat.File.MusicalInstruments.uk-Template.csv',
                            'Flat.File.MusicalInstruments.uk-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_musicalinstruments_browse_tree_guide.csv',
                        ),
                    ],

                    /* 'Music' => [
                        'title' => 'Music',
                        'meta_name' => 'Music',
                        'templates' => array(
                            'Flat.File.Music.uk-Template.csv',
                            'Flat.File.Music.uk-DataDefinitions.csv',
                            'Flat.File.Music.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    */

                    'Office' => [
                        'tmpl_id'    => 48,
                        'meta_name' => 'Office',
                        'title' => 'Office',
                        'templates' => array(
                            'Flat.File.Office.uk-Template.csv',
                            'Flat.File.Office.uk-DataDefinitions.csv',
                            'Flat.File.Office.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_office-products_browse_tree_guide.csv',
                        ),
                    ],
                    'PetSupplies' => [
                        'tmpl_id'    => 49,
                        'meta_name' => 'PetSupplies',
                        'title' => 'Pet Supplies',
                        'templates' => array(
                            'Flat.File.PetSupplies.uk-Template.csv',
                            'Flat.File.PetSupplies.uk-DataDefinitions.csv',
                            'Flat.File.PetSupplies.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_pet-supplies_browse_tree_guide.csv',
                        ),
                    ],
                    
                    // 'MusicalInstruments' => [
                    //     'meta_name' => 'MusicalInstruments',
                    //     'title' => 'Musical Instruments',
                    //     'templates' => array(
                    //         'Flat.File.MusicalInstruments.uk-Template.csv',
                    //         'Flat.File.MusicalInstruments.uk-DataDefinitions.csv',
                    //         'Flat.File.MusicalInstruments.uk-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'uk_musical-instruments_browse_tree_guide.csv',
                    //     ),
                    // ],
                   
                    'SWVG' => [
                        'tmpl_id'    => 55,
                        'meta_name' => 'SWVG',
                        'title' => 'Video Games',
                        'templates' => array(
                            'Flat.File.SWVG.uk-Template.csv',
                            'Flat.File.SWVG.uk-DataDefinitions.csv',
                            'Flat.File.SWVG.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_software_browse_tree_guide.csv',
                            'uk_games_browse_tree_guide.csv',
                        ),
                    ],
                    'Shoes' => [
                        'tmpl_id'    => 56,
                        'meta_name' => 'Shoes',
                        'title' => 'Shoes',
                        'templates' => array(
                            'Flat.File.Shoes.uk-Template.csv',
                            'Flat.File.Shoes.uk-DataDefinitions.csv',
                            'Flat.File.Shoes.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_shoes_browse_tree_guide.csv',
                        ),
                    ],
                    'Sports' => [
                        'tmpl_id'    => 57,
                        'meta_name' => 'Sports',
                        'title' => 'Sports',
                        'templates' => array(
                            'Flat.File.Sports.uk-Template.csv',
                            'Flat.File.Sports.uk-DataDefinitions.csv',
                            'Flat.File.Sports.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_sports_browse_tree_guide.csv',
                        ),
                    ],
                    // 'SoftwareVideoGames' => [
                    //     'title' => 'SoftwareVideoGames',
                    //     'meta_name' => 'SoftwareVideoGames',
                    //     'templates' => array(
                    //         'Flat.File.SoftwareVideoGames.uk-Template.csv',
                    //         'Flat.File.SoftwareVideoGames.uk-DataDefinitions.csv',
                    //         'Flat.File.SoftwareVideoGames.uk-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],
                    'Toys' => [
                        'tmpl_id'    => 58,
                        'meta_name' => 'Toys',
                        'title' => 'Toys',
                        'templates' => array(
                            'Flat.File.Toys.uk-Template.csv',
                            'Flat.File.Toys.uk-DataDefinitions.csv',
                            'Flat.File.Toys.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_kids_browse_tree_guide.csv',
                        ),
                    ],
                    'Video' => [
                        'tmpl_id'    => 0,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.uk-Template.csv',
                            'Flat.File.Video.uk-DataDefinitions.csv',
                            'Flat.File.Video.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    
                    'Watches' => [
                        'tmpl_id'    => 59,
                        'meta_name' => 'Watches',
                        'title' => 'Watches',
                        'templates' => array(
                            'Flat.File.Watches.uk-Template.csv',
                            'Flat.File.Watches.uk-DataDefinitions.csv',
                            'Flat.File.Watches.uk-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'uk_watches_browse_tree_guide.csv',
                        ),
                    ],
                    
                    // 'Musical Instrument Lite' => [
                    //     'meta_name' => 'Musical Instrument lite',
                    //     'title' => 'Musical Instrument lite',
                    //     'templates' => array(
                    //         'Flat.File.MusicInstruments-lite.uk-Template.csv',
                    //         'Flat.File.MusicInstruments-lite.uk-DataDefinitions.csv',
                    //         'Flat.File.MusicInstruments-lite.uk-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'uk_musicalinstruments_browse_tree_guide.csv',
                    //     ),
                    // ],

                    // 'Music' => [
                    //     'meta_name' => 'Music',
                    //     'title' => 'Music',
                    //     'templates' => array(
                    //         'Flat.File.Music.uk-Template.csv',
                    //         'Flat.File.Music.uk-DataDefinitions.csv',
                    //         'Flat.File.Music.uk-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],

                    
                )
            ),


            // amazon.fr (FR)
            'FR' => array(
                'site' => 'amazon.fr',
                'code' => 'FR',
                'categories' => array(
                    'AutoAccessry' => [
                        'tmpl_id'    => 116,
                        'meta_name' => 'AutoAccessory',
                        'title' => 'Auto Accessory',
                        'templates' => array(
                            'Flat.File.AutoAccessory.fr-Template.csv',
                            'Flat.File.AutoAccessory.fr-DataDefinitions.csv',
                            'Flat.File.AutoAccessory.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_automotive_browse_tree_guide.csv',
                        ),
                    ],
                    'Beauty' => [
                        'tmpl_id'    => 117,
                        'meta_name' => 'Beauty',
                        'title' => 'Beauty',
                        'templates' => array(
                            'Flat.File.Beauty.fr-Template.csv',
                            'Flat.File.Beauty.fr-DataDefinitions.csv',
                            'Flat.File.Beauty.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_beauty_browse_tree_guide.csv',
                        ),
                    ],
                    'BookLoader' => array(
                        'tmpl_id'    => 119,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.fr-Template.csv',
                            'Flat.File.BookLoader.fr-DataDefinitions.csv',
                            'Flat.File.BookLoader.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    'ConsumerElectronics' => [
                        'tmpl_id'    => 120,
                        'meta_name' => 'ConsumerElectronics',
                        'title' => 'Consumer Electronics',
                        'templates' => array(
                            'Flat.File.ConsumerElectronics.fr-Template.csv',
                            'Flat.File.ConsumerElectronics.fr-DataDefinitions.csv',
                            'Flat.File.ConsumerElectronics.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_electronics_browse_tree_guide.csv',
                        ),
                    ],
                    'Computers' => [
                        'tmpl_id'    => 121,
                        'meta_name' => 'Computers',
                        'title' => 'Computers',
                        'templates' => array(
                            'Flat.File.Computers.fr-Template.csv',
                            'Flat.File.Computers.fr-DataDefinitions.csv',
                            'Flat.File.Computers.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_computers_browse_tree_guide.csv',
                        ),
                    ],
                    'Clothing' => [
                        'tmpl_id'    => 122,
                        'meta_name' => 'Clothing',
                        'title' => 'Clothing',
                        'templates' => array(
                            'Flat.File.Clothing.fr-Template.csv',
                            'Flat.File.Clothing.fr-DataDefinitions.csv',
                            'Flat.File.Clothing.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodAndBeverages' => [
                        'tmpl_id'    => 123,
                        'meta_name' => 'FoodAndBeverages',
                        'title' => 'Food & Beverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages.fr-Template.csv',
                            'Flat.File.FoodAndBeverages.fr-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Health' => [
                        'tmpl_id'    => 124,
                        'meta_name' => 'Health',
                        'title' => 'Health',
                        'templates' => array(
                            'Flat.File.Health.fr-Template.csv',
                            'Flat.File.Health.fr-DataDefinitions.csv',
                            'Flat.File.Health.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_hpc_browse_tree_guide.csv',
                        ),
                    ],
                    // Bricolage
                    'HomeImprovement' => [
                        'tmpl_id'    => 125,
                        'title' => 'HomeImprovement',
                        'meta_name' => 'HomeImprovement',
                        'templates' => array(
                            'Flat.File.HomeImprovement.fr-Template.csv',
                            'Flat.File.HomeImprovement.fr-DataDefinitions.csv',
                            'Flat.File.HomeImprovement.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_tools_browse_tree_guide.csv',
                        ),
                    ],

                    'Home' => [
                        'tmpl_id'    => 126,
                        'meta_name' => 'Home',
                        'title' => 'Home',
                        'templates' => array(
                            'Flat.File.Home.fr-Template.csv',
                            'Flat.File.Home.fr-DataDefinitions.csv',
                            'Flat.File.Home.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_kitchen_browse_tree_guide.csv',
                        ),
                    ],
                    'Jewelry' => [
                        'tmpl_id'    => 127,
                        'meta_name' => 'Jewelry',
                        'title' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry.fr-Template.csv',
                            'Flat.File.Jewelry.fr-DataDefinitions.csv',
                            'Flat.File.Jewelry.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_jewelry_browse_tree_guide.csv',
                        ),
                    ],
                    'Lighting' => [
                        'tmpl_id'    => 128,
                        'meta_name' => 'Lighting',
                        'title' => 'Lighting',
                        'templates' => array(
                            'Flat.File.Lighting.fr-Template.csv',
                            'Flat.File.Lighting.fr-DataDefinitions.csv',
                            'Flat.File.Lighting.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_luminaires-eclairage_browse_tree_guide.csv',
                        ),
                    ],

                    'ListingLoader' => [
                        'tmpl_id'    => 139,
                        'meta_name' => 'Offer',
                        'title' => 'Listing Loader',
                        'btguides' => '',
                        'templates' => [
                            'ListingLoader-Template.csv',
                            'ListingLoader-DataDefinitions.csv',
                            'ListingLoader-ValidValues.csv'
                        ]
                    ],

                    'MusicalInstruments' => [
                        'tmpl_id'    => 129,
                        'meta_name' => 'MusicalInstruments',
                        'title' => 'Musical Instruments',
                        'templates' => array(
                            'Flat.File.MusicalInstruments.fr-Template.csv',
                            'Flat.File.MusicalInstruments.fr-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments.fr-ValidValues.csv',
                        ),
                        'btguides' => array(),
                    ],
                    'Music' => [
                        'tmpl_id'    => 130,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        'templates' => array(
                            'Flat.File.Music.fr-Template.csv',
                            'Flat.File.Music.fr-DataDefinitions.csv',
                            'Flat.File.Music.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],

                    'Office' => [
                        'tmpl_id'    => 131,
                        'meta_name' => 'Office',
                        'title' => 'Office',
                        'templates' => array(
                            'Flat.File.Office.fr-Template.csv',
                            'Flat.File.Office.fr-DataDefinitions.csv',
                            'Flat.File.Office.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_office-products_browse_tree_guide.csv',
                        ),
                    ],
                    'PetSupplies' => [
                        'tmpl_id'    => 132,
                        'meta_name' => 'PetSupplies',
                        'title' => 'Pet Supplies',
                        'templates' => array(
                            'Flat.File.PetSupplies.fr-Template.csv',
                            'Flat.File.PetSupplies.fr-DataDefinitions.csv',
                            'Flat.File.PetSupplies.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_pet-supplies_browse_tree_guide.csv',
                        ),
                    ],
                    'SWVG' => [
                        'tmpl_id'    => 133,
                        'meta_name' => 'SWVG',
                        'title' => 'Video Games',
                        'templates' => array(
                            'Flat.File.SWVG.fr-Template.csv',
                            'Flat.File.SWVG.fr-DataDefinitions.csv',
                            'Flat.File.SWVG.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_software_browse_tree_guide.csv',
                            'fr_videogames_browse_tree_guide.csv',
                        ),
                    ],
                    'Shoes' => [
                        'tmpl_id'    => 134,
                        'meta_name' => 'Shoes',
                        'title' => 'Shoes',
                        'templates' => array(
                            'Flat.File.Shoes.fr-Template.csv',
                            'Flat.File.Shoes.fr-DataDefinitions.csv',
                            'Flat.File.Shoes.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_shoes_browse_tree_guide.csv',
                        ),
                    ],
                    'Sports' => [
                        'tmpl_id'    => 135,
                        'meta_name' => 'Sports',
                        'title' => 'Sports',
                        'templates' => array(
                            'Flat.File.Sports.fr-Template.csv',
                            'Flat.File.Sports.fr-DataDefinitions.csv',
                            'Flat.File.Sports.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_sports_browse_tree_guide.csv',
                        ),
                    ],
                    'ToysBaby' => [
                        'tmpl_id'    => 136,
                        'meta_name' => 'ToysBaby',
                        'title' => 'Toys Baby',
                        'templates' => array(
                            'Flat.File.ToysBaby.fr-Template.csv',
                            'Flat.File.ToysBaby.fr-DataDefinitions.csv',
                            'Flat.File.ToysBaby.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'fr_baby_browse_tree_guide.csv',
                            'fr_toys_browse_tree_guide.csv',
                        ),
                    ],
                    'Video' => [
                        'tmpl_id'    => 137,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.fr-Template.csv',
                            'Flat.File.Video.fr-DataDefinitions.csv',
                            'Flat.File.Video.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Watches' => [
                        'tmpl_id'    => 138,
                        'title' => 'Watches',
                        'meta_name' => 'Watches',
                        'templates' => array(
                            'Flat.File.Watches.fr-Template.csv',
                            'Flat.File.Watches.fr-DataDefinitions.csv',
                            'Flat.File.Watches.fr-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    
                )
            ),


            // amazon.ca (CA)
            'CA' => array(
                'site' => 'amazon.ca',
                'code' => 'CA',
                'categories' => array(
                    'AutoAccessory' => [
                        'tmpl_id'    => 61,
                        'meta_name' => 'AutoAccessory',
                        'title' => 'Auto Accessory',
                        'templates' => array(
                            'Flat.File.AutoAccessory.ca-Template.csv',
                            'Flat.File.AutoAccessory.ca-DataDefinitions.csv',
                            'Flat.File.AutoAccessory.ca-ValidValues.csv',
                            // 'Flat.File.TiresAndWheels.ca-Template.csv',
                            // 'Flat.File.TiresAndWheels.ca-DataDefinitions.csv',
                            // 'Flat.File.TiresAndWheels.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_automotive_browse_tree_guide.csv',
                        ),
                    ],
                    'AutomatePricing' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'AutomatePricing',
                        'title' => 'AutomatePricing',
                        'templates' => array(
                            'Flat.File.AutomatePricing.ca-Template.csv',
                            'Flat.File.AutomatePricing.ca-DataDefinitions.csv',
                            'Flat.File.AutomatePricing.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Baby' => [
                        'tmpl_id'    => 63,
                        'meta_name' => 'Baby',
                        'title' => 'Baby',
                        'templates' => array(
                            'Flat.File.Baby.ca-Template.csv',
                            'Flat.File.Baby.ca-DataDefinitions.csv',
                            'Flat.File.Baby.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_baby-products_browse_tree_guide.csv',
                        ),
                    ],
                    // 'Baby.Industrial' => [
                    //     'meta_name' => 'Baby.Industrial',
                    //     'title' => 'Baby.Industrial',
                    //     'templates' => array(
                    //         'Flat.File.Baby.Industrial.ca-Template.csv',
                    //         'Flat.File.Baby.Industrial.ca-DataDefinitions.csv',
                    //         'Flat.File.Baby.Industrial.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],
                    'Beauty' => [
                        'tmpl_id'    => 64,
                        'meta_name' => 'Beauty',
                        'title' => 'Beauty',
                        'templates' => array(
                            'Flat.File.Beauty.ca-Template.csv',
                            'Flat.File.Beauty.ca-DataDefinitions.csv',
                            'Flat.File.Beauty.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_beauty_browse_tree_guide.csv',
                        ),
                    ],
                    'BookLoader' => array(
                        'tmpl_id'    => 65,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.ca-Template.csv',
                            'Flat.File.BookLoader.ca-DataDefinitions.csv',
                            'Flat.File.BookLoader.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    'CameraAndPhoto' => [
                        'tmpl_id'    => 66,
                        'meta_name' => 'CameraAndPhoto',
                        'title' => 'Camera & Photo',
                        'templates' => array(
                            'Flat.File.CameraAndPhoto.ca-Template.csv',
                            'Flat.File.CameraAndPhoto.ca-DataDefinitions.csv',
                            'Flat.File.CameraAndPhoto.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_ce_browse_tree_guide.csv',
                        ),
                    ],
                    'Clothing' => [
                        'tmpl_id'    => 67,
                        'meta_name' => 'Clothing',
                        'title' => 'Clothing',
                        'templates' => array(
                            'Flat.File.Clothing.ca-Template.csv',
                            'Flat.File.Clothing.ca-DataDefinitions.csv',
                            'Flat.File.Clothing.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'Computers' => [
                        'tmpl_id'    => 68,
                        'meta_name' => 'Computers',
                        'title' => 'Computers',
                        'templates' => array(
                            'Flat.File.Computers.ca-Template.csv',
                            'Flat.File.Computers.ca-DataDefinitions.csv',
                            'Flat.File.Computers.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_ce_browse_tree_guide.csv',
                        ),
                    ],
                    'ConsumerElectronics' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'ConsumerElectronics',
                        'title' => 'ConsumerElectronics',
                        'templates' => array(
                            'Flat.File.ConsumerElectronics.ca-Template.csv',
                            'Flat.File.ConsumerElectronics.ca-DataDefinitions.csv',
                            'Flat.File.ConsumerElectronics.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    // 'CE' => [
                    //     'meta_name' => 'CE',
                    //     'title' => 'CE',
                    //     'templates' => array(
                    //         'Flat.File.CE.ca-Template.csv',
                    //         'Flat.File.CE.ca-DataDefinitions.csv',
                    //         'Flat.File.CE.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],
                    'FoodAndBeverages' => [
                        'tmpl_id'    => 69,
                        'meta_name' => 'FoodAndBeverages',
                        'title' => 'Food & Beverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages.ca-Template.csv',
                            'Flat.File.FoodAndBeverages.ca-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_grocery_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodServiceAndJanSan' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'FoodServiceAndJanSan',
                        'title' => 'FoodServiceAndJanSan',
                        'templates' => array(
                            'Flat.File.FoodServiceAndJanSan.ca-Template.csv',
                            'Flat.File.FoodServiceAndJanSan.ca-DataDefinitions.csv',
                            'Flat.File.FoodServiceAndJanSan.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Health' => [
                        'tmpl_id'    => 70,
                        'meta_name' => 'Health',
                        'title' => 'Health',
                        'templates' => array(
                            'Flat.File.Health.ca-Template.csv',
                            'Flat.File.Health.ca-DataDefinitions.csv',
                            'Flat.File.Health.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_hpc_browse_tree_guide.csv',
                        ),
                    ],
                    'Home' => [
                        'tmpl_id'    => 71,
                        'meta_name' => 'Home',
                        'title' => 'Home',
                        'templates' => array(
                            'Flat.File.Home.ca-Template.csv',
                            'Flat.File.Home.ca-DataDefinitions.csv',
                            'Flat.File.Home.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_garden_browse_tree_guide.csv',
                            'ca_kitchen_browse_tree_guide.csv',
                        ),
                    ],
                    'HomeImprovement' => [
                        'tmpl_id'    => 72,
                        'meta_name' => 'HomeImprovement',
                        'title' => 'Home Improvement',
                        'btguides' => ['ca_tools_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.HomeImprovement.ca-DataDefinitions.csv',
                            'Flat.File.HomeImprovement.ca-Template.csv',
                            'Flat.File.HomeImprovement.ca-ValidValues.csv'
                        ]
                    ],
                    // 'Industrial' => [
                    //     'meta_name' => 'Industrial',
                    //     'title' => 'Industrial',
                    //     'templates' => array(
                    //         'Flat.File.Industrial.ca-Template.csv',
                    //         'Flat.File.Industrial.ca-DataDefinitions.csv',
                    //         'Flat.File.Industrial-lite.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],
                    // 'InventoryLoader' => [
                    //     'meta_name' => 'InventoryLoader',
                    //     'title' => 'InventoryLoader',
                    //     'templates' => array(
                    //         'Flat.File.InventoryLoader.ca-Template.csv',
                    //         'Flat.File.InventoryLoader.ca-DataDefinitions.csv',
                    //         'Flat.File.InventoryLoader-lite.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],
                    'Jewelry' => [
                        'tmpl_id'    => 73,
                        'meta_name' => 'Jewelry',
                        'title' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry.ca-Template.csv',
                            'Flat.File.Jewelry.ca-DataDefinitions.csv',
                            'Flat.File.Jewelry.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_jewelry_browse_tree_guide.csv',
                        ),
                    ],

                    'KindleAccessories' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'KindleAccessories',
                        'title' => 'KindleAccessories',
                        'templates' => array(
                            'Flat.File.KindleAccessories.ca-Template.csv',
                            'Flat.File.KindleAccessories.ca-DataDefinitions.csv',
                            'Flat.File.KindleAccessories-lite.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Kitchen' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Kitchen',
                        'title' => 'Kitchen',
                        'templates' => array(
                            'Flat.File.Kitchen.ca-Template.csv',
                            'Flat.File.Kitchen.ca-DataDefinitions.csv',
                            'Flat.File.Kitchen-lite.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                     'LargeAppliances' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'LargeAppliances',
                        'title' => 'LargeAppliances',
                        'templates' => array(
                            'Flat.File.LargeAppliances.ca-Template.csv',
                            'Flat.File.LargeAppliances.ca-DataDefinitions.csv',
                            'Flat.File.LargeAppliances-lite.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    
                    'Luggage' => [
                        'tmpl_id'    => 74,
                        'meta_name' => 'Luggage',
                        'title' => 'Luggage',
                        'templates' => array(
                            'Flat.File.Luggage.ca-Template.csv',
                            'Flat.File.Luggage.ca-DataDefinitions.csv',
                            'Flat.File.Luggage.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_luggage_browse_tree_guide.csv',
                        ),
                    ],

                    'ListingLoader' => [
                        'tmpl_id'    => 79,
                        'meta_name' => 'Offer',
                        'title' => 'Listing Loader',
                        'btguides' => '',
                        'templates' => [
                            'Flat.File.Listingloader.ca-Template.csv',
                            'Flat.File.Listingloader.ca-DataDefinitions.csv',
                            // 'Listingloader-ValidValues.csv'
                        ]
                    ],

                    // 'LabSupplies' => [
                    //     'meta_name' => 'LabSupplies',
                    //     'title' => 'LabSupplies',
                    //     'templates' => array(
                    //         'Flat.File.LabSupplies.ca-Template.csv',
                    //         'Flat.File.LabSupplies.ca-DataDefinitions.csv',
                    //         'Flat.File.LabSupplies-lite.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    // ],
                    'MusicalInstruments' => [
                        'tmpl_id'    => 75,
                        'meta_name' => 'MusicalInstruments',
                        'title' => 'Musical Instruments',
                        'btguides' => ['ca_musical-instruments_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Musicalinstruments.ca-Template.csv',
                            'Flat.File.Musicalinstruments.ca-DataDefinitions.csv',
                            'Flat.File.Musicalinstruments.ca-ValidValues.csv'
                        ]
                    ],
                    'Music' => [
                        'tmpl_id'    => 76,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        'templates' => array(
                            'Flat.File.music.ca-Template.csv',
                            'Flat.File.music.ca-DataDefinitions.csv',
                            'Flat.File.music.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Office' => [
                        'tmpl_id'    => 77,
                        'meta_name' => 'Office',
                        'title' => 'Office',
                        'btguides' => array(),
                        'templates' => [
                            'Flat.File.Office.ca-Template.csv',
                            'Flat.File.Office.ca-DataDefinitions.csv',
                            'Flat.File.Office.ca-ValidValues.csv'
                        ]
                    ],
                    'PetSupplies' => [
                        'tmpl_id'    => 78,
                        'meta_name' => 'PetSupplies',
                        'title' => 'Pet Supplies',
                        'templates' => array(
                            'Flat.File.PetSupplies.ca-Template.csv',
                            'Flat.File.PetSupplies.ca-DataDefinitions.csv',
                            'Flat.File.PetSupplies.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_pet-supplies_browse_tree_guide.csv',
                        ),
                    ],
                     'ProfessionalHealthCare' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'ProfessionalHealthCare',
                        'title' => 'ProfessionalHealthCare',
                        'templates' => array(
                            'Flat.File.ProfessionalHealthCare.ca-Template.csv',
                            'Flat.File.ProfessionalHealthCare.ca-DataDefinitions.csv',
                            'Flat.File.ProfessionalHealthCare.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    
                    // 'SWVG' => [
                    //     'meta_name' => 'SWVG',
                    //     'title' => 'Video Games',
                    //     'btguides' => ['ca_videogames_browse_tree_guide.csv'],
                    //     'templates' => [
                    //         'Flat.File.SWVG.ca-Template.csv',
                    //         'Flat.File.SWVG.ca-DataDefinitions.csv',
                    //         'Flat.File.SWVG.ca-ValidValues.csv'
                    //     ]
                    // ],
                    'Sports' => [
                        'tmpl_id'    => 81,
                        'meta_name' => 'Sports',
                        'title' => 'Sports',
                        'templates' => array(
                            'Flat.File.Sports.ca-Template.csv',
                            'Flat.File.Sports.ca-DataDefinitions.csv',
                            'Flat.File.Sports.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_sports_browse_tree_guide.csv',
                        ),
                    ],
                     'Shoes' => [
                        'meta_name' => 'Shoes',
                        'title' => 'Shoes',
                        'templates' => array(
                            'Flat.File.Shoes.ca-Template.csv',
                            'Flat.File.Shoes.ca-DataDefinitions.csv',
                            'Flat.File.Shoes.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'SoftwareVideoGames' => [
                        'tmpl_id'    => 80,
                        'meta_name' => 'SoftwareVideoGames',
                        'title' => 'SoftwareVideoGames',
                        'templates' => array(
                            'Flat.File.SoftwareVideoGames.ca-Template.csv',
                            'Flat.File.SoftwareVideoGames.ca-DataDefinitions.csv',
                            'Flat.File.SoftwareVideoGames.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'SportsMemorabilia' => [
                        'tmpl_id'    => 82,
                        'meta_name' => 'SportsMemorabilia',
                        'title' => 'Sports Memorabilia',
                        'btguides' => ['ca_sports_memorabilia_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.SportsMemorabilia.ca-Template.csv',
                            'Flat.File.SportsMemorabilia.ca-DataDefinitions.csv',
                            'Flat.File.SportsMemorabilia.ca-ValidValues.csv'
                        ]
                    ],
                    'Sports' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Sports',
                        'title' => 'Sports',
                        'btguides' => [''],
                        'templates' => [
                            'Flat.File.Sports.ca-Template.csv',
                            'Flat.File.Sports.ca-DataDefinitions.csv',
                            'Flat.File.Sports.ca-ValidValues.csv'
                        ]
                    ],
                    'Toys' => [
                        'tmpl_id'    => 83,
                        'meta_name' => 'Toys',
                        'title' => 'Toys',
                        'templates' => array(
                            'Flat.File.Toys.ca-Template.csv',
                            'Flat.File.Toys.ca-DataDefinitions.csv',
                            'Flat.File.Toys.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'ca_toys_browse_tree_guide.csv',
                        ),
                    ],

                    /** Comment For a While
                     * 
                     *
                     * 
                     **/
                    /* CA
                    'Video' => [
                        'tmpl_id'    => 84,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.ca-Template.csv',
                            'Flat.File.Video.ca-DataDefinitions.csv',
                            'Flat.File.Video.ca-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    */
                    // 'Watches' => [
                    //     'tmpl_id'    => 85,
                    //     'meta_name' => 'Watches',
                    //     'title' => 'Watches',
                    //     'templates' => array(
                    //         'Flat.File.Watches.ca-Template.csv',
                    //         'Flat.File.Watches.ca-DataDefinitions.csv',
                    //         'Flat.File.Watches.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'ca_watches_browse_tree_guide.csv',
                    //     ),
                    // ],
                    // 'Wireless' => [
                    //     'tmpl_id'    => 86,
                    //     'meta_name' => 'Wireless',
                    //     'title' => 'Wireless',
                    //     'templates' => array(
                    //         'Flat.File.Wireless.ca-Template.csv',
                    //         'Flat.File.Wireless.ca-DataDefinitions.csv',
                    //         'Flat.File.Wireless.ca-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'ca_ce_browse_tree_guide.csv',
                    //     ),
                    // ],
                ),
            ),


            // amazon.de (DE)
            'DE' => array(
                'site' => 'amazon.de',
                'code' => 'DE',
                'categories' => array(
                    'AutoAccessory' => [
                        'tmpl_id'    => 87,
                        'meta_name' => 'AutoAccessory',
                        'title' => 'Auto Accessory',
                        'btguides' => 'de_automotive_browse_tree_guide.csv',
                        'templates' => [
                            'Flat.File.AutoAccessory.de-Template.csv',
                            'Flat.File.AutoAccessory.de-DataDefinitions.csv',
                            'Flat.File.AutoAccessory.de-ValidValues.csv'
                        ]
                    ],
                    'Beauty' => [
                        'tmpl_id'    => 88,
                        'meta_name' => 'Beauty',
                        'title' => 'Beauty',
                        'templates' => array(
                            'Flat.File.Beauty.de-Template.csv',
                            'Flat.File.Beauty.de-DataDefinitions.csv',
                            'Flat.File.Beauty.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_beauty_browse_tree_guide.csv',
                        ),
                    ],
                     'BookLoader' => array(
                        'tmpl_id'    => 90,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.de-Template.csv',
                            'Flat.File.BookLoader.de-DataDefinitions.csv',
                            'Flat.File.BookLoader.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),

                    'Clothing' => [
                        'tmpl_id'    => 89,
                        'meta_name' => 'Clothing',
                        'title' => 'Clothing',
                        'templates' => array(
                            'Flat.File.Clothing.de-Template.csv',
                            'Flat.File.Clothing.de-DataDefinitions.csv',
                            'Flat.File.Clothing.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    
                    'Computers' => [
                        'tmpl_id'    => 91,
                        'meta_name' => 'Computers',
                        'title' => 'Computers',
                        'templates' => array(
                            'Flat.File.Computers.de-Template.csv',
                            'Flat.File.Computers.de-DataDefinitions.csv',
                            'Flat.File.Computers.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_computers_browse_tree_guide.csv',
                        ),
                    ],
                    'ConsumerElectronics' => [
                        'tmpl_id'    => 92,
                        'meta_name' => 'ConsumerElectronics',
                        'title' => 'Consumer Electronics',
                        'templates' => array(
                            'Flat.File.ConsumerElectronics.de-Template.csv',
                            'Flat.File.ConsumerElectronics.de-DataDefinitions.csv',
                            'Flat.File.ConsumerElectronics.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_ce_browse_tree_guide.csv',
                        ),
                    ],
                    'Eyewear' => [
                        'tmpl_id'    => 93,
                        'meta_name' => 'Eyewear',
                        'title' => 'Eyewear',
                        'templates' => array(
                            'Flat.File.Eyewear.de-Template.csv',
                            'Flat.File.Eyewear.de-DataDefinitions.csv',
                            'Flat.File.Eyewear.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodAndBeverages' => [
                        'tmpl_id'    => 94,
                        'meta_name' => 'FoodAndBeverages',
                        'title' => 'Food & Beverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages.de-Template.csv',
                            'Flat.File.FoodAndBeverages.de-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_food_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodServiceAndJanSan' => [
                        'tmpl_id'    => 95,
                        'meta_name' => 'FoodServiceAndJanSan',
                        'title' => 'FoodService & JanSan',
                        'btguides' => ['de_industrial_browse_tree_guide.json'],
                        'templates' => [
                            'Flat.File.FoodServiceAndJanSan.de-Template.csv',
                            'Flat.File.FoodServiceAndJanSan.de-DataDefinitions.csv',
                            'Flat.File.FoodServiceAndJanSan.de-ValidValues.csv'
                        ]
                    ],
                    'Health' => [
                        'tmpl_id'    => 96,
                        'meta_name' => 'Health',
                        'title' => 'Health',
                        'templates' => array(
                            'Flat.File.Health.de-Template.csv',
                            'Flat.File.Health.de-DataDefinitions.csv',
                            'Flat.File.Health.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_drugstore_browse_tree_guide.csv',
                        ),
                    ],
                    'Home' => [
                        'tmpl_id'    => 97,
                        'meta_name' => 'Home',
                        'title' => 'Home',
                        'templates' => array(
                            'Flat.File.Home.de-Template.csv',
                            'Flat.File.Home.de-DataDefinitions.csv',
                            'Flat.File.Home.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_garden_browse_tree_guide.csv',
                            'de_kitchen_browse_tree_guide.csv',
                        ),
                    ],
                    'HomeImprovement' => [
                        'tmpl_id'    => 98,
                        'meta_name' => 'HomeImprovement',
                        'title' => 'Home Improvement',
                        'btguides' => ['de_tools-sgp_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.HomeImprovement.de-Template.csv',
                            'Flat.File.HomeImprovement.de-DataDefinitions.csv',
                            'Flat.File.HomeImprovement.de-ValidValues.csv'
                        ]
                    ],
                    'Industrial' => [
                        'tmpl_id'    => 99,
                        'meta_name' => 'Industrial',
                        'title' => 'Industrial',
                        'btguides' => ['de_industrial_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.HomeImprovement.de-Template.csv',
                            'Flat.File.HomeImprovement.de-DataDefinitions.csv',
                            'Flat.File.HomeImprovement.de-ValidValues.csv'
                        ]
                    ],
                    'Jewelry' => [
                        'tmpl_id'    => 100,
                        'meta_name' => 'Jewelry',
                        'title' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry.de-Template.csv',
                            'Flat.File.Jewelry.de-DataDefinitions.csv',
                            'Flat.File.Jewelry.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_jewelry_browse_tree_guide.csv',
                        ),
                    ],
                    'Kitchen' => [
                        'tmpl_id'    => 101,
                        'meta_name' => 'Kitchen',
                        'title' => 'Kitchen',
                        'templates' => array(
                            'Flat.File.Kitchen.de-Template.csv',
                            'Flat.File.Kitchen.de-DataDefinitions.csv',
                            'Flat.File.Kitchen.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_kitchen_browse_tree_guide.csv',
                        ),
                    ],
                    'LabSupplies' => [
                        'tmpl_id'    => 102,
                        'meta_name' => 'LabSupplies',
                        'title' => 'Lab Supplies',
                        'btguides' => ['de_industrial_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.LabSupplies.de-Template.csv',
                            'Flat.File.LabSupplies.de-DataDefinitions.csv',
                            'Flat.File.LabSupplies.de-ValidValues.csv'
                        ]
                    ],
                    'LawnAndGarden' => [
                        'tmpl_id'    => 103,
                        'meta_name' => 'LawnAndGarden',
                        'title' => 'Lawn & Garden',
                        'btguides' => ['de_garden_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.LawnAndGarden.de-Template.csv',
                            'Flat.File.LawnAndGarden.de-DataDefinitions.csv',
                            'Flat.File.LawnAndGarden.de-ValidValues.csv'
                        ]
                    ],
                    'Lighting' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Lighting',
                        'title' => 'Lighting',
                        'btguides' => ['de_lighting_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Lighting.de-Template.csv',
                            'Flat.File.Lighting.de-DataDefinitions.csv',
                            'Flat.File.Lighting.de-ValidValues.csv'
                        ]
                    ],
                    'Luggage' => [
                        'tmpl_id'    => 104,
                        'meta_name' => 'Luggage',
                        'title' => 'Luggage',
                        'btguides' => ['de_luggage_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Luggage.de-Template.csv',
                            'Flat.File.Luggage.de-DataDefinitions.csv',
                            'Flat.File.Luggage.de-ValidValues.csv',
                        ]
                    ],

                    'ListingLoader' => [
                        'tmpl_id'    => 109,
                        'meta_name' => 'Offer',
                        'title' => 'Listing Loader',
                        'btguides' => '',
                        'templates' => [
                            'ListingLoader-Template.csv',
                            'ListingLoader-DataDefinitions.csv',
                            'ListingLoader-ValidValues.csv'
                        ]
                    ],

                    'MusicalInstruments' => [
                        'tmpl_id'    => 105,
                        'meta_name' => 'MusicalInstruments',
                        'title' => 'Musical Instruments',
                        'btguides' => 'de_musical-instruments_browse_tree_guide.csv',
                        'templates' => [
                            'Flat.File.MusicalInstruments.de-Template.csv',
                            'Flat.File.MusicalInstruments.de-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments.de-ValidValues.csv'
                        ]
                    ],
                    'Music' => [
                        'tmpl_id'    => 106,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        'templates' => array(
                            'Flat.File.Music.de-Template.csv',
                            'Flat.File.Music.de-DataDefinitions.csv',
                            'Flat.File.Music.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Office' => [
                        'tmpl_id'    => 107,
                        'meta_name' => 'Office',
                        'title' => 'Office',
                        'templates' => array(
                            'Flat.File.Office.de-Template.csv',
                            'Flat.File.Office.de-DataDefinitions.csv',
                            'Flat.File.Office.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_office-products_browse_tree_guide.csv',
                        ),
                    ],
                    'PetSupplies' => [
                        'tmpl_id'    => 108,
                        'meta_name' => 'PetSupplies',
                        'title' => 'Pet Supplies',
                        'btguides' => ['de_pet-supplies_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.PetSupplies.de-Template.csv',
                            'Flat.File.PetSupplies.de-DataDefinitions.csv',
                            'Flat.File.PetSupplies.de-ValidValues.csv'
                        ]
                    ],
                    
                    'SWVG' => [
                        'tmpl_id'    => 110,
                        'meta_name' => 'SWVG',
                        'title' => 'Video Games',
                        'btguides' => ['de_videogames_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.SWVG.de-Template.csv',
                            'Flat.File.SWVG.de-DataDefinitions.csv',
                            'Flat.File.SWVG.de-ValidValues.csv'
                        ]
                    ],
                    'Shoes' => [
                        'tmpl_id'    => 111,
                        'meta_name' => 'Shoes',
                        'title' => 'Shoes',
                        'btguides' => ['de_shoes_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Shoes.de-Template.csv',
                            'Flat.File.Shoes.de-DataDefinitions.csv',
                            'Flat.File.Shoes.de-ValidValues.csv'
                        ]
                    ],
                    'Sports' => [
                        'tmpl_id'    => 112,
                        'meta_name' => 'Sports',
                        'title' => 'Sports',
                        'btguides' => ['de_sports_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Sports.de-Template.csv',
                            'Flat.File.Sports.de-DataDefinitions.csv',
                            'Flat.File.Sports.de-ValidValues.csv'
                        ]
                    ],
                    'ToysBaby' => [
                        'tmpl_id'    => 113,
                        'meta_name' => 'ToysBaby',
                        'title' => 'Toys Baby',
                        'templates' => array(
                            'Flat.File.ToysBaby.de-Template.csv',
                            'Flat.File.ToysBaby.de-DataDefinitions.csv',
                            'Flat.File.ToysBaby.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_baby_browse_tree_guide.csv',
                            'de_toys_browse_tree_guide.csv',
                        ),
                    ],
                    'Video' => [
                        'tmpl_id'    => 114,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.de-Template.csv',
                            'Flat.File.Video.de-DataDefinitions.csv',
                            'Flat.File.Video.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Watches' => [
                        'tmpl_id'    => 115,
                        'meta_name' => 'Watches',
                        'title' => 'Watches',
                        'templates' => array(
                            'Flat.File.Watches.de-Template.csv',
                            'Flat.File.Watches.de-DataDefinitions.csv',
                            'Flat.File.Watches.de-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'de_watches_browse_tree_guide.csv',
                        ),
                    ],
                )
            ), // amazon.de

            // amazon.es (ES)
            'ES' => array(
                'site' => 'amazon.es',
                'code' => 'ES',
                'categories' => array(
                    'AutoAccessory' => [
                        'tmpl_id'    => 140,
                        'meta_name' => 'AutoAccessory',
                        'title' => 'Auto Accessory',
                        'templates' => array(
                            'Flat.File.AutoAccessory.es-Template.csv',
                            'Flat.File.AutoAccessory.es-DataDefinitions.csv',
                            'Flat.File.AutoAccessory.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_automotive_browse_tree_guide.csv',
                        ),
                    ],
                    'Baby' => [
                        'tmpl_id'    => 141,
                        'meta_name' => 'Baby',
                        'title' => 'Baby',
                        'templates' => array(
                            'Flat.File.Baby.es-Template.csv',
                            'Flat.File.Baby.es-DataDefinitions.csv',
                            'Flat.File.Baby.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_baby-products_browse_tree_guide.csv',
                        ),
                    ],
                    'Beauty' => [
                        'tmpl_id'    => 142,
                        'meta_name' => 'Beauty',
                        'title' => 'Beauty',
                        'templates' => array(
                            'Flat.File.Beauty.es-Template.csv',
                            'Flat.File.Beauty.es-DataDefinitions.csv',
                            'Flat.File.Beauty.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_beauty_browse_tree_guide.csv',
                        ),
                    ],
                    'BookLoader' => array(
                        'tmpl_id'    => 143,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.es-Template.csv',
                            'Flat.File.BookLoader.es-DataDefinitions.csv',
                            'Flat.File.BookLoader.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    'ConsumerElectronics' => [
                        'tmpl_id'    => 144,
                        'meta_name' => 'ConsumerElectronics',
                        'title' => 'Electrónica y accesorios de electrónica',
                        'templates' => array(
                            'Flat.File.CE.es-Template.csv',
                            'Flat.File.CE.es-DataDefinitions.csv',
                            'Flat.File.CE.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_electronics_browse_tree_guide.csv',
                        ),
                    ],
                    'Clothing' => [
                        'tmpl_id'    => 145,
                        'meta_name' => 'Clothing',
                        'title' => 'Clothing',
                        'templates' => array(
                            'Flat.File.Clothing.es-Template.csv',
                            'Flat.File.Clothing.es-DataDefinitions.csv',
                            'Flat.File.Clothing.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'Computers' => [
                        'tmpl_id'    => 146,
                        'meta_name' => 'Computers',
                        'title' => 'Computers',
                        'templates' => array(
                            'Flat.File.Computers.es-Template.csv',
                            'Flat.File.Computers.es-DataDefinitions.csv',
                            'Flat.File.Computers.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_computers_browse_tree_guide.csv',
                        ),
                    ],
                    'Eyewear' => [
                        'tmpl_id'    => 147,
                        'meta_name' => 'Eyewear',
                        'title' => 'Eyewear',
                        'templates' => array(
                            'Flat.File.Eyewear.es-Template.csv',
                            'Flat.File.Eyewear.es-DataDefinitions.csv',
                            'Flat.File.Eyewear.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_apparel_browse_tree_guide.csv',
                        ),
                    ],
                    'FoodAndBeverages' => [
                        'tmpl_id'    => 0,
                        'title' => 'FoodAndBeverages',
                        'meta_name' => 'FoodAndBeverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages.es-Template.csv',
                            'Flat.File.FoodAndBeverages.es-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'grocery_browse_tree_guide.csv',
                        ),
                    ],
                    'Health' => [
                        'tmpl_id'    => 148,
                        'meta_name' => 'Health',
                        'title' => 'Health',
                        'templates' => array(
                            'Flat.File.Health.es-Template.csv',
                            'Flat.File.Health.es-DataDefinitions.csv',
                            'Flat.File.Health.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_health_browse_tree_guide.csv',
                        ),
                    ],
                    'Home' => [
                        'tmpl_id'    => 149,
                        'meta_name' => 'Home',
                        'title' => 'Home',
                        'templates' => array(
                            'Flat.File.Home.es-Template.csv',
                            'Flat.File.Home.es-DataDefinitions.csv',
                            'Flat.File.Home.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_kitchen_browse_tree_guide.csv',
                        ),
                    ],
                    'Jewelry' => [
                        'tmpl_id'    => 150,
                        'meta_name' => 'Jewelry',
                        'title' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry.es-Template.csv',
                            'Flat.File.Jewelry.es-DataDefinitions.csv',
                            'Flat.File.Jewelry.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_jewelry_browse_tree_guide.csv',
                        ),
                    ],
                    'Kitchen' => [
                        'tmpl_id'    => 150,
                        'meta_name' => 'Kitchen',
                        'title' => 'Kitchen',
                        'templates' => array(
                            'Flat.File.Kitchen.es-Template.csv',
                            'Flat.File.Kitchen.es-DataDefinitions.csv',
                            'Flat.File.Kitchen.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_kitchen_browse_tree_guide.csv',
                        ),
                    ],
                    'LabSupplies' => [
                        'tmpl_id'    => 152,
                        'meta_name' => 'LabSupplies',
                        'title' => 'Lab Supplies',
                        'btguides' => ['es_industrial_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.LabSupplies.es-Template.csv',
                            'Flat.File.LabSupplies.es-Template.csv',
                            'Flat.File.LabSupplies.es-ValidValues.csv'
                        ]
                    ],
                    'LawnAndGarden' => [
                        'tmpl_id'    => 153,
                        'meta_name' => 'LawnAndGarden',
                        'title' => 'Jardín',
                        'templates' => array(
                            'Flat.File.LawnAndGarden.es-Template.csv',
                            'Flat.File.LawnAndGarden.es-DataDefinitions.csv',
                            'Flat.File.LawnAndGarden.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_garden_browse_tree_guide.csv',
                        ),
                    ],
                    'Lighting' => [
                        'tmpl_id'    => 154,
                        'meta_name' => 'Lighting',
                        'title' => 'Iluminación',
                        'templates' => array(
                            'Flat.File.Lighting.es-Template.csv',
                            'Flat.File.Lighting.es-DataDefinitions.csv',
                            'Flat.File.Lighting.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_lighting_browse_tree_guide.csv',
                        ),
                    ],
                    'ListingLoader' => [
                        'tmpl_id'    => 159,
                        'meta_name' => 'Offer',
                        'title' => 'Listing Loader',
                        'btguides' => '',
                        'templates' => [
                            'ListingLoader.es-Template.csv',
                            'ListingLoader.es-DataDefinitions.csv',
                            'ListingLoader.es-ValidValues.csv'
                        ]
                    ],
                    'Luggage' => [
                        'tmpl_id'    => 155,
                        'meta_name' => 'Luggage',
                        'title' => 'Luggage',
                        'btguides' => ['es_luggage_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Luggage.es-Template.csv',
                            'Flat.File.Luggage.es-DataDefinitions.csv',
                            'Flat.File.Luggage.es-ValidValues.csv'

                        ]
                    ],
                    'MusicalInstruments' => [
                        'tmpl_id'    => 156,
                        'meta_name' => 'MusicalInstruments',
                        'title' => 'Musical Instruments',
                        'btguides' => ['es_musical-instruments_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.MusicalInstruments.es-Template.csv',
                            'Flat.File.MusicalInstruments.es-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments.es-ValidValues.csv'
                        ]
                    ],
                    'Music' => [
                        'tmpl_id'    => 157,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        'templates' => array(
                            'Flat.File.Music.es-Template.csv',
                            'Flat.File.Music.es-DataDefinitions.csv',
                            'Flat.File.Music.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Office' => [
                        'tmpl_id'    => 158,
                        'meta_name' => 'Office',
                        'title' => 'Office',
                        'btguides' => ['es_office-products_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.Office.es-Template.csv',
                            'Flat.File.Office.es-DataDefinitions.csv',
                            'Flat.File.Office.es-ValidValues.csv'
                        ]
                    ],
                     'PetSupplies' => [
                        'tmpl_id'    => 0,
                        'title' => 'Pet Supplies',
                        'meta_name' => 'PetSupplies',
                        'templates' => [
                            'Flat.File.PetSupplies.es-Template.csv',
                            'Flat.File.PetSupplies.es-DataDefinitions.csv',
                            'Flat.File.PetSupplies.es-ValidValues.csv',
                        ]
                        // 'btguides' => array(
                        //     'pet-supplies_browse_tree_guide.csv',
                        // ),
                    ],
                    
                    'SWVG' => [
                        'tmpl_id'    => 160,
                        'meta_name' => 'SWVG',
                        'title' => 'Video Games',
                        'btguides' => ['es_videogames_browse_tree_guide.csv'],
                        'templates' => [
                            'Flat.File.SWVG.es-Template.csv',
                            'Flat.File.SWVG.es-DataDefinitions.csv',
                            'Flat.File.SWVG.es-ValidValues.csv'
                        ]
                    ],
                    'Shoes' => [
                        'tmpl_id'    => 161,
                        'meta_name' => 'Shoes',
                        'title' => 'Shoes',
                        'btguides' => 'es_shoes_browse_tree_guide.csv',
                       'templates' => [
                            'Flat.File.Shoes.es-Template.csv',
                            'Flat.File.Shoes.es-DataDefinitions.csv',
                            'Flat.File.Shoes.es-ValidValues.csv'
                        ]
                    ],
                    'Sports' => [
                        'tmpl_id'    => 162,
                        'meta_name' => 'Sports',
                        'title' => 'Zapatos y Complementos',
                        'templates' => array(
                            'Flat.File.Shoes.es-Template.csv',
                            'Flat.File.Shoes.es-DataDefinitions.csv',
                            'Flat.File.Shoes.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_shoes_browse_tree_guide.csv',
                        ),
                    ],
                    'Tools' => [
                        'tmpl_id'    => 163,
                        'meta_name' => 'Tools',
                        'title' => 'Bricolaje y Herramientas',
                        'templates' => array(
                            'Flat.File.Tools.es-Template.csv',
                            'Flat.File.Tools.es-DataDefinitions.csv',
                            'Flat.File.Tools.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_tools_browse_tree_guide.csv',
                        ),
                    ],
                    'Toys' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Toys',
                        'title' => 'Juguetes y Juegos',
                        'templates' => array(
                            'Flat.File.Toys.es-Template.csv',
                            'Flat.File.Toys.es-DataDefinitions.csv',
                            'Flat.File.Toys.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_toys_browse_tree_guide.csv',
                        ),
                    ],
                    'Video' => [
                        'tmpl_id'    => 164,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.es-Template.csv',
                            'Flat.File.Video.es-DataDefinitions.csv',
                            'Flat.File.Video.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'Watches' => [
                        'tmpl_id'    => 165,
                        'meta_name' => 'Watches',
                        'title' => 'Relojes',
                        'templates' => array(
                            'Flat.File.Watches.es-Template.csv',
                            'Flat.File.Watches.es-DataDefinitions.csv',
                            'Flat.File.Watches.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'es_watches_browse_tree_guide.csv',
                        ),
                    ],
                ),
            ), // amazon.es

            // amazon.it (IT)
            'IT' => array(
                'site' => 'amazon.it',
                'code' => 'IT',
                'categories' => array(

                    // Automotive Parts & Accessories
                    'AutoAccessory' => array(
                        'tmpl_id'    => 167,
                        'title' => 'AutoAccessory',
                        'templates' => array(
                            'Flat.File.AutoAccessory.it-Template.csv',
                            'Flat.File.AutoAccessory.it-DataDefinitions.csv',
                            'Flat.File.AutoAccessory.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_automotive_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'AutoAccessory'
                    ),

                    // Baby
                    'Baby' => array(
                        'tmpl_id'    => 168,
                        'title' => 'Baby',
                        'templates' => array(
                            'Flat.File.Baby.it-Template.csv',
                            'Flat.File.Baby.it-DataDefinitions.csv',
                            'Flat.File.Baby.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_baby-products_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Baby'
                    ),
                    'BookLoader' => array(
                        'tmpl_id'    => 169,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.it-Template.csv',
                            'Flat.File.BookLoader.it-DataDefinitions.csv',
                            'Flat.File.BookLoader.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    'Books' => array(
                        'tmpl_id'    => 170,
                        'title' => 'Books',
                        'templates' => array(
                            'Flat.File.Books.it-Template.csv',
                            'Flat.File.Books.it-DataDefinitions.csv',
                            'Flat.File.Books.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'Books'
                    ),

                    // Clothing & Accessories
                    'Clothing' => array(
                        'tmpl_id'    => 171,
                        'title' => 'Clothing & Accessories',
                        'templates' => array(
                            'Flat.File.Clothing.it-Template.csv',
                            'Flat.File.Clothing.it-DataDefinitions.csv',
                            'Flat.File.Clothing.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_apparel_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Clothing'
                    ),

                    // Consumer Electronics
                    'ConsumerElectronics' => array(
                        'tmpl_id'    => 172,
                        'title' => 'Electronics',
                        'templates' => array(
                            'Flat.File.CE.it-Template.csv',
                            'Flat.File.CE.it-DataDefinitions.csv',
                            'Flat.File.CE.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_computers_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'ConsumerElectronics'
                    ),

                    //Eyewear
                    'Eyewear' => array(
                        'tmpl_id'    => 173,
                        'title' => 'Eyewear',
                        'templates' => array(
                            'Flat.File.Eyewear.it-Template.csv',
                            'Flat.File.Eyewear.it-DataDefinitions.csv',
                            'Flat.File.Eyewear.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Eyewear'
                    ),

                    'FoodAndBeverages' => array(
                        'tmpl_id'    => 0,
                        'title' => 'FoodAndBeverages',
                        'templates' => array(
                            'Flat.File.FoodAndBeverages.it-Template.csv',
                            'Flat.File.FoodAndBeverages.it-DataDefinitions.csv',
                            'Flat.File.FoodAndBeverages.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'FoodAndBeverages'
                    ),

                    'FoodServiceAndJanSan' => array(
                        'tmpl_id'    => 0,
                        'title' => 'FoodServiceAndJanSan',
                        'templates' => array(
                            'Flat.File.FoodServiceAndJanSan.it-Template.csv',
                            'Flat.File.FoodServiceAndJanSan.it-DataDefinitions.csv',
                            'Flat.File.FoodServiceAndJanSan.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'FoodServiceAndJanSan'
                    ),

                    'Home' => array(
                        'tmpl_id'    => 174,
                        'title' => 'Home',
                        'templates' => array(
                            'Flat.File.Home.it-Template.csv',
                            'Flat.File.Home.it-DataDefinitions.csv',
                            'Flat.File.Home.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Home'
                    ),
                    'Health' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Health',
                        'templates' => array(
                            'Flat.File.Health.it-Template.csv',
                            'Flat.File.Health.it-DataDefinitions.csv',
                            'Flat.File.Health.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Health'
                    ),
                    'Industrial' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Industrial',
                        'templates' => array(
                            'Flat.File.Industrial.it-Template.csv',
                            'Flat.File.Industrial.it-DataDefinitions.csv',
                            'Flat.File.Industrial.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Industrial'
                    ),

                    'Jewelry' => array(
                        'tmpl_id'    => 175,
                        'title' => 'Jewelry',
                        'templates' => array(
                            'Flat.File.Jewelry.it-Template.csv',
                            'Flat.File.Jewelry.it-DataDefinitions.csv',
                            'Flat.File.Jewelry.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Jewelry'
                    ),

                    // KindleAccessories
                    'KindleAccessories' => array(
                        'tmpl_id'    => 176,
                        'title' => 'Kindle Accessories',
                        'templates' => array(
                            'Flat.File.KindleAccessories.it-Template.csv',
                            'Flat.File.KindleAccessories.it-DataDefinitions.csv',
                            'Flat.File.KindleAccessories.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_grocery_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'KindleAccessories'
                    ),
                    
                    'Kitchen' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Kitchen',
                        'templates' => array(
                            'Flat.File.Kitchen.it-Template.csv',
                            'Flat.File.Kitchen.it-DataDefinitions.csv',
                            'Flat.File.Kitchen.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'Kitchen'
                    ),


                    // LawnAndGarden
                    'LawnAndGarden' => array(
                        'tmpl_id'    => 178,
                        'title' => 'Lawn And Garden',
                        'templates' => array(
                            'Flat.File.LawnAndGarden.it-Template.csv',
                            'Flat.File.LawnAndGarden.it-DataDefinitions.csv',
                            'Flat.File.LawnAndGarden.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'LawnAndGarden'
                    ),
                    'LabSupplies' => array(
                        'tmpl_id'    => 0,
                        'title' => 'LabSupplies',
                        'templates' => array(
                            'Flat.File.LabSupplies.it-Template.csv',
                            'Flat.File.LabSupplies.it-DataDefinitions.csv',
                            'Flat.File.LabSupplies.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'LabSupplies'
                    ),

                    'Lighting' => array(
                        'tmpl_id'    => 179,
                        'title' => 'Lighting',
                        'templates' => array(
                            'Flat.File.Lighting.it-Template.csv',
                            'Flat.File.Lighting.it-DataDefinitions.csv',
                            'Flat.File.Lighting.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Lighting'
                    ),

                    'Luggage' => array(
                        'tmpl_id'    => 180,
                        'title' => 'Luggage',
                        'templates' => array(
                            'Flat.File.Luggage.it-Template.csv',
                            'Flat.File.Luggage.it-DataDefinitions.csv',
                            'Flat.File.Luggage.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_health_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Luggage'
                    ),

                    'MusicalInstruments' => array(
                        'tmpl_id'    => 181,
                        'title' => 'Musical Instruments',
                        'templates' => array(
                            'Flat.File.MusicalInstruments.it-Template.csv',
                            'Flat.File.MusicalInstruments.it-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_kitchen_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'MusicalInstruments'
                    ),

                    'Music' => [
                        'tmpl_id'    =>182,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        'templates' => array(
                            'Flat.File.Music.it-Template.csv',
                            'Flat.File.Music.it-DataDefinitions.csv',
                            'Flat.File.Music.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],

                    'Motorcycles' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Motorcycles',
                        'title' => 'Motorcycles',
                        'templates' => array(
                            'Flat.File.Motorcycles.it-Template.csv',
                            'Flat.File.Motorcycles.it-DataDefinitions.csv',
                            'Flat.File.Motorcycles.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    'MotoApparel' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'MotoApparel',
                        'title' => 'MotoApparel',
                        'templates' => array(
                            'Flat.File.MotoApparel.it-Template.csv',
                            'Flat.File.MotoApparel.it-DataDefinitions.csv',
                            'Flat.File.MotoApparel.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],

                    'Office' => array(
                        'tmpl_id'    => 183,
                        'title' => 'Office',
                        'templates' => array(
                            'Flat.File.Office.it-Template.csv',
                            'Flat.File.Office.it-DataDefinitions.csv',
                            'Flat.File.Office.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Office'
                    ),

                    'PersonalCareAppliances' => array(
                        'tmpl_id'    => 184,
                        'title' => 'Personal Care Appliances',
                        'templates' => array(
                            'Flat.File.PersonalCareAppliances.it-Template.csv',
                            'Flat.File.PersonalCareAppliances.it-DataDefinitions.csv',
                            'Flat.File.PersonalCareAppliances.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_jewelry_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'PersonalCareAppliances'
                    ),


                    'Shoes' => array(
                        'tmpl_id'    => 185,
                        'title' => 'Shoes',
                        'templates' => array(
                            'Flat.File.Shoes.it-Template.csv',
                            'Flat.File.Shoes.it-DataDefinitions.csv',
                            'Flat.File.Shoes.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Shoes'
                    ),

                    'Sports' => array(
                        'tmpl_id'    => 186,
                        'title' => 'Sports',
                        'templates' => array(
                            'Flat.File.Sports.it-Template.csv',
                            'Flat.File.Sports.it-DataDefinitions.csv',
                            'Flat.File.Sports.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Sports'
                    ),

                    'SWVG' => array(
                        'tmpl_id'    => 187,
                        'title' => 'Software',
                        'templates' => array(
                            'Flat.File.SWVG.it-Template.csv',
                            'Flat.File.SWVG.it-DataDefinitions.csv',
                            'Flat.File.SWVG.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'SWVG'
                    ),

                    'Tools' => array(
                        'tmpl_id'    => 188,
                        'title' => 'Tools',
                        'templates' => array(
                            'Flat.File.Tools.it-Template.csv',
                            'Flat.File.Tools.it-DataDefinitions.csv',
                            'Flat.File.Tools.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Tools'
                    ),

                    'ToysBaby' => array(
                        'tmpl_id'    => 189,
                        'title' => 'Toys Baby',
                        'templates' => array(
                            'Flat.File.ToysBaby.it-Template.csv',
                            'Flat.File.ToysBaby.it-DataDefinitions.csv',
                            'Flat.File.ToysBaby.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'ToysBaby'
                    ),
                    'TiresAndWheels' => array(
                        'tmpl_id'    => 0,
                        'title' => 'TiresAndWheels',
                        'templates' => array(
                            'Flat.File.TiresAndWheels.it-Template.csv',
                            'Flat.File.TiresAndWheels.it-DataDefinitions.csv',
                            'Flat.File.TiresAndWheels.it-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'TiresAndWheels'
                    ),
                    'Video' => [
                        'tmpl_id'    => 190,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.es-Template.csv',
                            'Flat.File.Video.es-DataDefinitions.csv',
                            'Flat.File.Video.es-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    // Office Products
                    'Watches' => array(
                        'tmpl_id'    => 191,
                        'title' => 'Watches',
                        'templates' => array(
                            'Flat.File.Watches.it-Template.csv',
                            'Flat.File.Watches.it-DataDefinitions.csv',
                            'Flat.File.Watches.it-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_office_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Watches'
                    ),
                ), //amazon.it
            ),

            'AU'    => array(
                'site'  => 'amazon.com.au',
                'code'  => 'AU',
                'categories' => array(

                    // Automotive Parts & Accessories
                    'AutoAccessory' => array(
                        'tmpl_id'    => 0,
                        'title' => 'AutoAccessory',
                        'templates' => array(
                            'Flat.File.AutoAccessry.au-Template.csv',
                            'Flat.File.AutoAccessry.au-DataDefinitions.csv',
                            'Flat.File.AutoAccessry.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_automotive_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'AutoAccessory'
                    ),

                    // Baby
                    'Baby' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Baby',
                        'templates' => array(
                            'Flat.File.Baby.au-Template.csv',
                            'Flat.File.Baby.au-DataDefinitions.csv',
                            'Flat.File.Baby.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_baby-products_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Baby'
                    ),
                    'BookLoader' => array(
                        'tmpl_id'    => 194,
                        'title' => 'BookLoader',
                        'templates' => array(
                            'Flat.File.BookLoader.au-Template.csv',
                            'Flat.File.BookLoader.au-DataDefinitions.csv',
                            'Flat.File.BookLoader.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'BookLoader'
                    ),
                    // 'Books' => array(
                    //     'title' => 'Books',
                    //     'templates' => array(
                    //         'Flat.File.Books.au-Template.csv',
                    //         'Flat.File.Books.au-DataDefinitions.csv',
                    //         'Flat.File.Books.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         '',
                    //     ),
                    //     'meta_name' => 'Books'
                    // ),

                    // Clothing & Accessories
                    'Clothing' => array(
                        'tmpl_id'    => 192,
                        'title' => 'Clothing & Accessories',
                        'templates' => array(
                            'Flat.File.Clothing.au-Template.csv',
                            'Flat.File.Clothing.au-DataDefinitions.csv',
                            'Flat.File.Clothing.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_apparel_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Clothing'
                    ),

                    // Consumer Electronics
                    'ConsumerElectronics' => array(
                        'tmpl_id'    => 197,
                        'title' => 'Electronics',
                        'templates' => array(
                            'Flat.File.ConsumerElectronics.au-Template.csv',
                            'Flat.File.ConsumerElectronics.au-DataDefinitions.csv',
                            'Flat.File.ConsumerElectronics.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_computers_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'ConsumerElectronics'
                    ),

                    //Eyewear
                    // 'Eyewear' => array(
                    //     'title' => 'Eyewear',
                    //     'templates' => array(
                    //         'Flat.File.Eyewear.au-Template.csv',
                    //         'Flat.File.Eyewear.au-DataDefinitions.csv',
                    //         'Flat.File.Eyewear.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'in_gift-cards_browse_tree_guide.csv',
                    //     ),
                    //     'meta_name' => 'Eyewear'
                    // ),
                    'Home' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Home',
                        'templates' => array(
                            'Flat.File.Home.au-Template.csv',
                            'Flat.File.Home.au-DataDefinitions.csv',
                            'Flat.File.Home.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Home'
                    ),

                    'HomeImprovement' => array(
                        'tmpl_id'    => 0,
                        'title' => 'HomeImprovement',
                        'templates' => array(
                            'Flat.File.HomeImprovement.au-Template.csv',
                            'Flat.File.HomeImprovement.au-DataDefinitions.csv',
                            'Flat.File.HomeImprovement.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_gift-cards_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'HomeImprovement'
                    ),

                    // 'Jewelry' => array(
                    //     'title' => 'Jewelry',
                    //     'templates' => array(
                    //         'Flat.File.Jewelry.au-Template.csv',
                    //         'Flat.File.Jewelry.au-DataDefinitions.csv',
                    //         'Flat.File.Jewelry.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'in_gift-cards_browse_tree_guide.csv',
                    //     ),
                    //     'meta_name' => 'Jewelry'
                    // ),

                    // KindleAccessories
                    // 'KindleAccessories' => array(
                    //     'title' => 'Kindle Accessories',
                    //     'templates' => array(
                    //         'Flat.File.KindleAccessories.au-Template.csv',
                    //         'Flat.File.KindleAccessories.au-DataDefinitions.csv',
                    //         'Flat.File.KindleAccessories.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'in_grocery_browse_tree_guide.csv',
                    //     ),
                    //     'meta_name' => 'KindleAccessories'
                    // ),

                    // LawnAndGarden
                    // 'LawnAndGarden' => array(
                    //     'title' => 'Lawn And Garden',
                    //     'templates' => array(
                    //         'Flat.File.LawnAndGarden.au-Template.csv',
                    //         'Flat.File.LawnAndGarden.au-DataDefinitions.csv',
                    //         'Flat.File.LawnAndGarden.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(),
                    //     'meta_name' => 'LawnAndGarden'
                    // ),

                    'Lighting' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Lighting',
                        'templates' => array(
                            'Flat.File.Lighting.au-Template.csv',
                            'Flat.File.Lighting.au-DataDefinitions.csv',
                            'Flat.File.Lighting.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Lighting'
                    ),

                    'ListingLoader' => [
                        'tmpl_id'    => 0,
                        'meta_name' => 'Offer',
                        'title' => 'Listing Loader',
                        'btguides' => '',
                        'templates' => [
                            'Listingloader-Template.csv',
                            'Listingloader-DataDefinitions.csv',
                            // 'Listingloader-ValidValues.csv'
                        ]
                    ],

                    // 'Luggage' => array(
                    //     'title' => 'Luggage',
                    //     'templates' => array(
                    //         'Flat.File.Luggage.au-Template.csv',
                    //         'Flat.File.Luggage.au-DataDefinitions.csv',
                    //         'Flat.File.Luggage.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'in_health_browse_tree_guide.csv',
                    //     ),
                    //     'meta_name' => 'Luggage'
                    // ),

                    'MusicalInstruments' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Musical Instruments',
                        'templates' => array(
                            'Flat.File.MusicalInstruments.au-Template.csv',
                            'Flat.File.MusicalInstruments.au-DataDefinitions.csv',
                            'Flat.File.MusicalInstruments.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_kitchen_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'MusicalInstruments'
                    ),

                    'Music' => [
                        'tmpl_id'    => 198,
                        'meta_name' => 'Music',
                        'title' => 'Music',
                        'templates' => array(
                            'Flat.File.Music.au-Template.csv',
                            'Flat.File.Musics.au-DataDefinitions.csv',
                            'Flat.File.Musics.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],

                    'Office' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Office',
                        'templates' => array(
                            'Flat.File.Office.au-Template.csv',
                            'Flat.File.Office.au-DataDefinitions.csv',
                            'Flat.File.Office.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Office'
                    ),

                    'Outdoors' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Outdoors',
                        'templates' => array(
                            'Flat.File.Outdoors.au-Template.csv',
                            'Flat.File.Outdoors.au-DataDefinitions.csv',
                            'Flat.File.Outdoors.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Outdoors'
                    ),

                    // 'PersonalCareAppliances' => array(
                    //     'title' => 'Personal Care Appliances',
                    //     'templates' => array(
                    //         'Flat.File.PersonalCareAppliances.au-Template.csv',
                    //         'Flat.File.PersonalCareAppliances.au-DataDefinitions.csv',
                    //         'Flat.File.PersonalCareAppliances.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(
                    //         'in_jewelry_browse_tree_guide.csv',
                    //     ),
                    //     'meta_name' => 'PersonalCareAppliances'
                    // ),


                    'Shoes' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Shoes',
                        'templates' => array(
                            'Flat.File.Shoes.au-Template.csv',
                            'Flat.File.Shoes.au-DataDefinitions.csv',
                            'Flat.File.Shoes.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Shoes'
                    ),

                    'Sports' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Sports',
                        'templates' => array(
                            'Flat.File.Sports.au-Template.csv',
                            'Flat.File.Sports.au-DataDefinitions.csv',
                            'Flat.File.Sports.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'Sports'
                    ),

                    'SoftwareVideoGames' => array(
                        'tmpl_id'    => 0,
                        'title' => 'SoftwareVideoGames',
                        'templates' => array(
                            'Flat.File.SoftwareVideoGames.au-Template.csv',
                            'Flat.File.SoftwareVideoGames.au-DataDefinitions.csv',
                            'Flat.File.SoftwareVideoGames.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'SoftwareVideoGames'
                    ),

                    // 'SWVG' => array(
                    //     'title' => 'Software',
                    //     'templates' => array(
                    //         'Flat.File.SWVG.au-Template.csv',
                    //         'Flat.File.SWVG.au-DataDefinitions.csv',
                    //         'Flat.File.SWVG.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(),
                    //     'meta_name' => 'SWVG'
                    // ),

                    // 'Tools' => array(
                    //     'title' => 'Tools',
                    //     'templates' => array(
                    //         'Flat.File.Tools.au-Template.csv',
                    //         'Flat.File.Tools.au-DataDefinitions.csv',
                    //         'Flat.File.Tools.au-ValidValues.csv',
                    //     ),
                    //     'btguides' => array(),
                    //     'meta_name' => 'Tools'
                    // ),

                    'ToysBaby' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Toys',
                        'templates' => array(
                            'Flat.File.Toys.au-Template.csv',
                            'Flat.File.Toys.au-DataDefinitions.csv',
                            'Flat.File.Toys.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'ToysBaby'
                    ),
                     'Tires And Wheels' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Tires And Wheels',
                        'templates' => array(
                            'Flat.File.TiresAndWheels.au.csv',
                            'Flat.File.TiresAndWheels.au-DataDefinitions.csv',
                            'Flat.File.TiresAndWheels.au-ValidValues.csv',
                        ),
                        'btguides' => array(),
                        'meta_name' => 'TiresAndWheels'
                    ),
                    'Video' => [
                        'tmpl_id'    => 199,
                        'title' => 'Video',
                        'meta_name' => 'Video',
                        'templates' => array(
                            'Flat.File.Video.au-Template.csv',
                            'Flat.File.Video.au-DataDefinitions.csv',
                            'Flat.File.Video.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                    ],
                    // Office Products
                    'Watches' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Watches',
                        'templates' => array(
                            'Flat.File.Watches.au-Template.csv',
                            'Flat.File.Watches.au-DataDefinitions.csv',
                            'Flat.File.Watches.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            'in_office_browse_tree_guide.csv',
                        ),
                        'meta_name' => 'Watches'
                    ),
                     'Wireless' => array(
                        'tmpl_id'    => 0,
                        'title' => 'Wireless',
                        'templates' => array(
                            'Flat.File.Wireless.au-Template.csv',
                            'Flat.File.Wireless.au-DataDefinitions.csv',
                            'Flat.File.Wireless.au-ValidValues.csv',
                        ),
                        'btguides' => array(
                            '',
                        ),
                        'meta_name' => 'Wireless'
                    ),
                ), //amazon.it
            ),
        );

        return $file_index;
    }

    function userPreferences(){
        global $wpdb;
        $table = $wpdb->prefix."amwscp_amazon_accounts";
        $sql = $wpdb->prepare("SELECT market_code FROM $table WHERE active = %d",[1]);
        $code = $wpdb->get_var($sql);
        if (!$code)
            $this->code = 'US';
        $this->code = $code;
    }
    function column_default($item, $column_name)
    {

        switch ($column_name) {
            case 'title':
                return $item[$column_name];
            case 'id':
                return  $item['order'];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item)
    {
        // if(in_array(strtolower($item['meta_name']), $this->importedTemplates)){
        //    $actions['null'] = "Imported";
        // }
        if(isset($item['status']) && $item['status']=="Imported"){
           $actions['null'] = "Imported";
           $actions['remove'] =  sprintf('<a href="admin.php?page=amwscpf-feed-template&action=remove_template&tmp_id='.$item['id'].'" >Remove</a>');
        }
        else{

            $actions['import'] = sprintf('<a class="importTemplatehref" href="?page=%s&action=%s&tpl=%s&tmpl_id=%d">Import</a>', $_REQUEST['page'], 'import',$this->code.'_'.$item['meta_name'],$item['tmpl_id']);
        }
        //Build row actions

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/
            $item['title'],
            /*$2%s*/
            $this->row_actions($actions,1)
        );
    }

    function column_cb($item)
    {
        /*return sprintf(
            '<input type="checkbox" name="%1$s" value="0" />',
            'amwscpf_cat-' . $this->code . $item['meta_name']
        );*/
        return;
    }

    function get_columns()
    {
        $columns = array(
//            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'title' => 'Amazon Product Categories for '.$this->countryfullname,
            'id'    => 'id',
        );
        return $columns;
    }

    function prepare_items($template=array())
    {

        /*
            foreach ($template as $key => $value) {
                if($value->country==$this->code) $this->importedTemplates[]=strtolower($value->tpl_name);
            }
        */
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 15;

        $columns = $this->get_columns();

        $hidden = array();
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->process_bulk_action();

        $this->get_items();
        $data = !empty($this->categories) ? $this->categories : array();
        // echo "<pre>";
        // print_r($data);exit;
        $j = count($template);

        $i = 0;
        foreach ($data as $key => $value) {

            $data[$key]['order'] = $j;
            $j++;
            
            foreach ($template as $k => $t) {

                if(strtoupper($t->country)==strtoupper($this->code)){
                    if($t->tmpl_id==$value['tmpl_id']){
                        $data[$key]['status'] = "Imported";
                        $data[$key]['id'] =  $t->id;
                        $data[$key]['order'] = $i;
                        $i++;
                    }
                }
            }

        }

        function usort_reorder($a, $b)
        {
            /*if(isset($a['status'])){
                print_r($a);exit;
               return $a;
            }
            elseif(isset($b['status'])){
                return $b;
            }
            else{*/

                $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'order'; //If no sort, default to title
                $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
                // $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
                if($a[$orderby]>$b[$orderby]){
                    return $a;
                }

            /* 
               return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
            }
            */
            
        }

        usort($data, 'usort_reorder');


        $current_page = $this->get_pagenum();

        $total_items = count($data);
        $per_page = $total_items;

        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;

        $this->datatablesdata = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

    function get_items()
    {
        $data = self::get_list();
        $country_code = $this->code;

        if (!isset($data[$country_code])) return false;
        $list = $data[$country_code];
        $this->categories = $list['categories'];
        $this->site = $list['site'];
        return true;
    }

    function process_bulk_action()
    {
        if ($this->current_action()) {
            $action = $this->current_action();

            switch ($action){
                case 'import':
                    $templates = $_REQUEST['tpl'];
                    $result = $this->importCategory($templates);
                    if($result==true){
                       $sendback = admin_url('admin.php?page=').$_REQUEST['page'].'&response=200';
                        if (isset($_REQUEST['need_help'])){
                            $sendback = admin_url('admin.php?page=exportfeed-amazon-amwscpf-admin&help=true&step=3');
                        }
                        wp_redirect($sendback);
                        break; 
                    }
                    $sendback = admin_url('admin.php?page=').$_REQUEST['page'];
                    if (isset($_REQUEST['need_help'])){
                        $sendback = admin_url('admin.php?page=exportfeed-amazon-amwscpf-admin&help=true&step=3');
                    }
                    wp_redirect($sendback);
                    break;
            }
        }
    }

    function importCategory($templates){
        global $wpdb;
        $tmpl = explode("_",$templates);
        $file_index = self::get_list();
        
        $category_name = $tmpl[1];
        $site_code = $tmpl[0];
        $imported = 0;
        $site = $file_index[$site_code];
       
        $this->code = $site_code;

        if(!isset($site['categories'][$category_name])) wp_die('no category');

        $category = $site['categories'][$category_name];
       
        return $this->importTemplates($category['templates'],$site_code);
    }

    function importTemplates($category,$site_code){
        $this->tpl_id = 0;
        $url = 'http://services.exportfeed.com/dev/mws/TPL/'.$site_code.'/';
//      $url = 'http://services.exportfeed.com/mws/TPLV2/'.$site_code.'/';
        $count = false;
        foreach ($category as $filename){
            $local_file = $this->getRemoteFiles($url.$filename);
            if (!$local_file) continue;
            $count = $this->importTplFile($local_file);
            $this->imported_count++;
            unlink($local_file);
        }
        return true;
    }

    function getRemoteFiles($filename){
        $response = wp_remote_get($filename,['timeout'=>100]);
        
        $response_code = wp_remote_retrieve_response_code($response);
        if($response_code!=200){
            $response_message = wp_remote_retrieve_response_message($response);
            print_r($response_message);
            $sendback = admin_url('admin.php?page=').$_REQUEST['page'].'&response='.$response_code.'&message=test';
            wp_redirect($sendback);
            exit;

        }
        
        if(is_wp_error($response)){
            return false;
        }
        #return $response;
        // get uploads folder
        $upload_dir  = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];

        // save file in uploads folder
        $local_file = trailingslashit( $upload_path ) . basename( $filename );

        #return wp_remote_retrieve_body($response);
        if ( ! file_put_contents( $local_file, wp_remote_retrieve_body( $response ) ) ) {
            exit;
            return false;
        }
        return $local_file;
    }

    function importTplFile($file){
        $mode = 'template'; // mode is set according to filename in array of templates
        $filename = strtolower(basename($file));
        if ( strpos( $filename, 'data'   ) > 0 ) $mode = 'data';
        if ( strpos( $filename, 'values' ) > 0 ) $mode = 'values';


        $template_name = str_replace('flat.file.','',basename($filename));
        $template_name = substr($template_name,0,strpos($template_name,'-'));
        $template_name = substr($template_name,0,strpos($template_name,'.'));
        if ($template_name == 'listingloader') $template_name = 'Offer';
        if ($template_name == 'ce') $template_name = 'ConsumerElectronics';
        if ($template_name == 'swvg') $template_name = 'SoftwareVideoGames';
        if ($template_name == 'sports') $template_name = 'Sports';

        switch ($mode) {
            case 'template':
                $tpl = $this->readFeedTemplate($file);
                
                $this->tpl_id = $this->parseFeedTemplate($tpl);
                $result = 1;
                break;

            case 'data':
                // read CSV file
                $field_data = $this->readFeedDataCSV( $file );

                // parse feed data defintions
                $result = $this->parseFeedData( $field_data, $template_name );
                $result = 1;
                break;

            case 'values':

                // read CSV file
                $field_data = $this->readFeedValuesCSV( $file );

                // parse feed valid values
                $result = $this->parseFeedValues( $field_data, $template_name );
                $result = 1;
                break;
        }
    }

    // Encoding issues, so we'll get file contents with this custom function
    function file_get_contents_utf8($fn) {
        $content = file_get_contents($fn);
        return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }

    function readFeedTemplate($file){
        $delimiter = ',';
        if ( ! file_exists($file) || ! is_readable($file) ) {
            echo "<pre>Could not read $file</pre>";
            die;
            #return false;
        }

        // getting raw content of template
        $tpl_str = $this->file_get_contents_utf8($file);
        
        $raw = str_replace(",","\t",$tpl_str);

        $tpl = new stdClass();
        $tpl->fields = array();
        $tpl->raw = $raw;
        $header =false;
        $line = 0;

        if ( ( $handle = fopen($file, 'r') ) !== false ) {

            // read lines
            while ( ( $row = fgetcsv($handle, 0, $delimiter) ) !== false ) {
                if ( $line == 0 ) {
                    $tpl->type    = str_replace( 'TemplateType=', '', utf8_encode($row[0]) );
                    $tpl->version = str_replace( 'Version=', '', utf8_encode($row[1]) );
                    if ( $tpl->type == 'sports') $tpl->type = 'Sports'; // fix Sports UK tpl
                } elseif ( $line == 1 ) {
                    // $header_labels = $row;
                    $myrow = array();
                    foreach ($row as $value) {
                        $myrow[] = utf8_encode($value);
                    }
                    $header_labels = $myrow;

                } elseif ( $line == 2 ) {

                    // third row contains field names
                    // $tpl->fields = array_combine( $row, $header_labels );
                    $myrow = array();
                    foreach ($row as $value) {
                        $myrow[] = utf8_encode($value);
                    }
                    $header_labels = $myrow;
                    $tpl->fields = array_combine( $myrow, $header_labels );

                }

                $line++;
            }
            fclose($handle);
        }
        
        return $tpl;
    }

    function parseFeedTemplate($tpl){
        
        global $wpdb;
        $templates_table = $wpdb->prefix . 'amwscp_amazon_templates';
        $fields_table    = $wpdb->prefix . 'amwscp_template_values';

        $tpl_id = $wpdb->get_var("SELECT id FROM $templates_table WHERE tpl_name = '{$tpl->type}' AND country = '{$this->code}'");
        if ($tpl_id){
            $wpdb->delete($fields_table,['tmpl_id'=>$tpl_id]);
        }

        $data = [
            'tpl_name' => $tpl->type,
            'tmpl_id'  => $_REQUEST['tmpl_id'],
            'version' => $tpl->version,
            'country' => $this->code,
            'raw'     => $tpl->raw
        ];

        if ($tpl_id) {
            $result = $wpdb->update($templates_table,$data,['id'=>$tpl_id,'country'=>$this->code]);
        } else {
            $result = $wpdb->insert($templates_table,$data);
            $tpl_id = $wpdb->insert_id;
        }

        // store fields
        foreach ($tpl->fields as $fields => $labels){
            $data = array();
            $data['fields'] = $fields; // values are altered
            $data['labels'] = $labels; // values are altered
            $data['tmpl_id'] = $tpl_id;
            $data['country'] = $this->code;

            $wpdb->insert($fields_table,$data);
        }
        return $tpl_id;
    }

    function readFeedDataCSV( $filename, $delimiter = ',' ) {
        if ( ! file_exists($filename) || ! is_readable($filename) ) {
            echo "<pre>Could not read $filename</pre>";
            return false;
        }
        $fields        = array();
        $current_group = '';
        $line          = 0;

        // open file
        if ( ( $handle = fopen($filename, 'r') ) !== false ) {

            // read lines
            while ( ( $row = fgetcsv($handle, 0, $delimiter) ) !== false ) {
                // first two row contains nothing of interest
                if ( $line < 2 ) {
                    $line++;
                    continue;
                }

                // parse row
                $group      = $row[0];
                $field      = $row[1];
                $label      = $row[2];
                $definition = $row[3];
                $accepted   = $row[4];
                $example    = $row[5];
                $required   = $row[6];

                if ( $group ) {

                    // parse group title
                    $current_group    = $group;
                    if ( strpos( $group, ' - ' ) > 0 ) {
                        $current_group_id = substr($group, 0, strpos($group,' - ') );
                    } else {
                        $current_group_id = 'Ungrouped';
                    }

                } else {

                    // check for multi-fields like 'bullet_point1 - bullet_point5'
                    if ( strpos( $field, '1 - ' ) > 0 ) {

                        $base_field = substr( $field, 0, strpos($field,'1 - ') );
                        $base_label = substr( $label, 0, strpos($label,'1 - ') );
                        $last_index = substr( $field, -1, 1 );

                        // create all fields
                        for ($i=1; $i <= $last_index; $i++) {

                            $field = $base_field . $i;
                            $label = $base_label .' '. $i;
                            $fields[ $field ] = array(
                                'definition' => $definition,
                                'examples'    => $example,
                                'required'   => $required == 'Required' ? 1:0,
                            );

                        }

                    } else {

                        // parse field information
                        $fields[ $field ] = array(
                            'definition' => $definition,
                            'examples'    => $example,
                            'required'   => $required == 'Required' ? 1:0,
                        );

                    } // if single field

                } // if group header

                $line++;
            }
            fclose($handle);
        }
        #echo "<pre>";print_r($fields);die;
        return $fields;
    }

    function parseFeedData($fields,$feedtype){
        global $wpdb;
        $templates_table = $wpdb->prefix . 'amwscp_amazon_templates';
        $fields_table    = $wpdb->prefix . 'amwscp_template_values';

        // get template_id
        $tpl_id = $wpdb->get_var("SELECT id FROM $templates_table WHERE tpl_name = '{$feedtype}' AND country = '{$this->code}'");

        foreach ($fields as $key => $field_data){
            $result = $wpdb->update($fields_table,$field_data,['fields'=>$key,'tmpl_id' => $tpl_id]);
        }
        return $tpl_id;
    }

    function readFeedValuesCSV( $filename, $delimiter = ',' ) {

        if ( ! file_exists($filename) || ! is_readable($filename) ) {
            echo "<pre>Could not read $filename</pre>";
            return false;
        }

        $fields        = array();
        $line          = 0;

        // open file
        if ( ( $handle = fopen($filename, 'r') ) !== false ) {

            // read lines
            while ( ( $row = fgetcsv($handle, 0, $delimiter) ) !== false ) {
                // echo "<pre>line $line: ";print_r($row);echo"</pre>";#die();

                if ( $line == 0 ) {
                    // nothing to do here
                } elseif ( $line == 1 ) {

                    // second row contains field names
                    for ($i=0; $i < sizeof($row); $i++) {
                        $fields[$i]['field'] = $row[$i];
                        $fields[$i]['values'] = array();
                    }

                } else {

                    // other row contain allowed values
                    for ($i=0; $i < sizeof($row); $i++) {
                        if ( $row[$i] )
                            $fields[$i]['values'][] = $row[$i];
                    }

                }

                $line++;
            }
            fclose($handle);
        }

        return $fields;
    }

    function parseFeedValues( $fields, $feed_type ) {
        global $wpdb;
        $templates_table = $wpdb->prefix . 'amwscp_amazon_templates';
        $fields_table    = $wpdb->prefix . 'amwscp_template_values';

        //get the template id
        $tpl_id = $wpdb->get_var("SELECT id FROM $templates_table WHERE tpl_name = '{$feed_type}' AND country ='{$this->code}'");

        #echo "<pre>";print_r($fields);die;

        foreach ($fields as $field_data){
            $data = array();
            $data['valid_values'] = maybe_serialize($field_data['values']);

            $result = $wpdb->update($fields_table,$data,[
                'tmpl_id'   => $tpl_id,
                'fields'    => $field_data['field'],
                'country'      => $this->code,
            ]);

        }
        return $tpl_id;
    }

    function getImportedTemplates($fullcountryname){
        $this->countryfullname= $fullcountryname;
        $where = 1;
        $single = false;
        global $wpdb;
        $table = $wpdb->prefix."amwscp_amazon_templates";
        $sql = "SELECT * FROM $table WHERE " . $where;

        if ($single)
            $templates = $wpdb->get_row($sql);
        else
            $templates = $wpdb->get_results($sql);
        return $templates;
    }

    function delete_template($id){
        global $wpdb;
        $table = $wpdb->prefix."amwscp_amazon_templates";
        $value_tbl = $wpdb->prefix."amwscp_template_values";

        $wpdb->delete($table,['id'=>$id]);
        $wpdb->delete($value_tbl,['tmpl_id'=>$id]);
    }
}