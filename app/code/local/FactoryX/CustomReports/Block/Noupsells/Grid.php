<?phpclass FactoryX_CustomReports_Block_Noupsells_Grid extends Mage_Adminhtml_Block_Widget_Grid{	protected $filterArray = array();    public function __construct()    {        parent::__construct();        $this->setId('noupsellsReportGrid');        $this->setDefaultSort('entity_id');        $this->setDefaultDir('desc');    }		public function fillArray($args)	{		$product_with_upsell_id = $args['row']['product_id'];					if (!in_array($product_with_upsell_id, $this->filterArray)) 		{			array_push($this->filterArray, $product_with_upsell_id);		}	}		public function getFilterUpsellsItems()    {		$upsellsItemsCollection = Mage::getModel('catalog/product_link')->useUpSellLinks()									->getLinkCollection()									->addFieldToSelect('product_id');											// Call iterator walk method with collection query string and callback method as parameters		// Has to be used to handle massive collection instead of foreach		Mage::getSingleton('core/resource_iterator')->walk($upsellsItemsCollection->getSelect(), array(array($this, 'fillArray')));		        return $this->filterArray;    }    protected function _prepareCollection()    {        $collection = Mage::getResourceModel('catalog/product_collection')						->addAttributeToSelect('entity_id')						->addAttributeToSelect('sku')						->addAttributeToSelect('name')						->addAttributeToSelect('status')						->addAttributeToSelect('visibility')						->addAttributeToFilter('type_id', array('eq' => 'configurable'))						->addAttributeToFilter('entity_id',array('nin'=>$this->getFilterUpsellsItems()));								//Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);		//Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);        $this->setCollection($collection);        parent::_prepareCollection();        return $this;    }    protected function _prepareColumns()    {        $this->addColumn('entity_id', array(            'header'    =>Mage::helper('reports')->__('Product ID'),            'width'     =>'50px',            'index'     =>'entity_id'        ));        $this->addColumn('name', array(            'header'    =>Mage::helper('reports')->__('Product Name'),			'width'     =>'50px',            'index'     =>'name'        ));				 $this->addColumn('sku', array(            'header'    =>Mage::helper('reports')->__('Sku'),			'width'     =>'50px',            'index'     =>'sku'        ));				$this->addColumn('visibility',            array(                'header'=> Mage::helper('catalog')->__('Visibility'),                'width' => '70px',                'index' => 'visibility',                'type'  => 'options',                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),        ));        $this->addColumn('status',            array(                'header'=> Mage::helper('catalog')->__('Status'),                'width' => '70px',                'index' => 'status',                'type'  => 'options',                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),        ));				$this->addColumn('action',            array(                'header'    =>  Mage::helper('reports')->__('Action'),                'width'     => '100',                'type'      => 'action',                'getter'    => 'getId',                'actions'   => array(                    array(                        'caption'   => Mage::helper('reports')->__('Edit Product'),                        'url'       => array('base'=> 'adminhtml/catalog_product/edit'),                        'field'     => 'id'                    )                ),                'filter'    => false,                'sortable'  => false,                'index'     => 'stores',                'is_system' => true,        ));        $this->addExportType('*/*/exportNoupsellsCsv', Mage::helper('reports')->__('CSV'));        $this->addExportType('*/*/exportNoupsellsExcel', Mage::helper('reports')->__('Excel'));        return parent::_prepareColumns();    }}