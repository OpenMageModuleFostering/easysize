<?php

	class Easysize_Webapp_Block_Attr extends Mage_Core_Block_Template{

		private $sizeCode;
		private $genderCode;
		private $attrObj;

		/**
		* @description : get theme 
		**/
		public function getVersion()
		{
			return Mage::getSingleton('core/design_package')->getTheme('frontend');
		}


		/**
		* @notice : the product need to have a field manufacturer, gender, and size
		* admin panel for "gender" & "size"
		* Manufacturer it'sn't a default attribute but already implemented in magento framework
		**/
		public function getProductObject(){

			$id = $this->getRequest()->getParam('id');
			$current_product=Mage::getModel('catalog/product')->load($id); 			

			$this->sizeCode = Mage::getStoreConfig('easysize_section/easysize_group/size_field',Mage::app()->getStore());
			$this->genderCode = Mage::getStoreConfig('easysize_section/easysize_group/gender_field',Mage::app()->getStore());


			if(!$current_product->isConfigurable() || !$this->attributesExist($current_product, $this->genderCode)) return false;

			$this->attrObj = new stdClass();
			
			$this->attrObj->product_id =$current_product->getSku();

			$id = $this->getRequest()->getParam('id');
			
			// Order button
			$this->attrObj->order_button_id = strlen(Mage::getStoreConfig('easysize_section/easysize_group/add_to_cart_field',Mage::app()->getStore())) > 2 ? Mage::getStoreConfig('easysize_section/easysize_group/add_to_cart_field',Mage::app()->getStore()) : ".add-to-cart-buttons";
			
			$this->attrObj->product_brand = $current_product->getAttributeText('manufacturer');
			
			$ncat =  $current_product->getCategoryIds();
			$this->attrObj->product_type = array();
			
			for ($i=0; $i < sizeof($ncat); $i++) { 
				$this->attrObj->product_type[] = Mage::getModel('catalog/category')->load($ncat[$i])->getName();
			}

			$this->attrObj->product_type = implode(",", $this->attrObj->product_type);
			$this->attrObj->product_gender = $current_product->getAttributeText($this->genderCode);
			$this->attrObj->user_id = $this->getUser();
			$this->attrObj->size_selector = "attribute".$this->getSizeNumberAttribute();
			$this->attrObj->placeholder = Mage::getStoreConfig('easysize_section/easysize_group/placeholder_field',Mage::app()->getStore());


			$this->attrObj->custom_style = Mage::getStoreConfig('easysize_section/easysize_group/style_field',Mage::app()->getStore());
			$this->attrObj->shop_id = Mage::getStoreConfig('easysize_section/easysize_group/id_shop_field',Mage::app()->getStore());

			$attributes = $current_product->getAttributes();
			$childProducts = Mage::getModel('catalog/product_type_configurable')
								->getUsedProducts(null,$current_product);

			$sizeObj = array();
			foreach($childProducts as $child) {
				if($this->attributesExist($child, $this->sizeCode)){
					$chid = $child->getId();
					$current_child_product=Mage::getModel('catalog/product')->load($chid);
					$qty =  $current_child_product->getStockItem()->getQty();
					$size =  $current_child_product->getAttributeText($this->sizeCode); 
					$sizeObj[$size] = $qty;
				}
			}
			$this->attrObj->sizes_in_stock = $sizeObj;

			
			if(!empty($izeObj) || !$this->attrObj->product_brand || !$this->attrObj->product_gender){
				return false;
			}else{
				return true;
				
			}

		}
		public function getObj(){
			return json_encode($this->attrObj);
		}

		public function getSizeNumberAttribute(){
			$this->sizeCode = Mage::getStoreConfig('easysize_section/easysize_group/size_field',Mage::app()->getStore());
			$tize = Mage::getSingleton("eav/config")->getAttribute('catalog_product',    $this->sizeCode);
			$attrSize = $tize->getData();

			return $attrSize['attribute_id'];
		}

		public function getUser(){
			 if(Mage::getSingleton('customer/session')->isLoggedIn()) {
				 $customerData = Mage::getSingleton('customer/session')->getCustomer();
				  return $customerData->getId();
			 }else{
				return -1;
			 }
		}

		public function attributesExist($product, $attrCode){
			$attributes = $product->getAttributes();

			foreach ($attributes as $attribute){
				if($attribute->getAttributecode()==$attrCode){
					return true;
				}
			}
			return false;
		}

		public function getProductId(){
			return $this->getRequest()->getParam('id');
		}

		public function checkCart(){
			
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			$cartItems = $quote->getAllVisibleItems();
			$arr = Array();
			foreach ($cartItems as $item){
				$productId = $item->getProductId();
				array_push($arr, $productId);
			}

			return json_encode($arr);
		}

		public function attributeExistsInTheShop($code){
			
			$attributes = Mage::getModel('catalog/product')->getAttributes();
			$attributeArray = array();

			foreach($attributes as $a){

				foreach ($a->getEntityType()->getAttributeCodes() as $attributeName) {
					array_push($attributeArray, $attributeName);
				}
				break;
			}
			return in_array($code, $attributeArray);
		}

		public function getAllProducts($sizeCode, $genderCode){
		
			$allProducts = Array();
			$products = Mage::getModel('catalog/product')->getCollection();
			$_productCollection = clone $products;
			$_productCollection->clear()
							   ->addAttributeToFilter('type_id', 'configurable')
							   ->load();

			foreach($_productCollection as $prod) {
				$obj= new stdClass();
				$valid = true;
				$product = Mage::getModel('catalog/product')->load($prod->getId());

				$obj->name = $product->name;
				if($this->attributesExist($product, $genderCode)){
					$obj->gender = $product->getAttributeText($genderCode);
				}else{
					$obj->gender = "No";
					$valid = false;
				}

				$ncat =  $product->getCategoryIds()[0];
				$product_type =  Mage::getModel('catalog/category')->load($ncat)->getName();
				if($product_type){
					$obj->product_type = $product_type;
				}else{
					$obj->product_type = "No";
					$valid = false;
				}

				$obj->children = "No";
				$childProducts = Mage::getModel('catalog/product_type_configurable')
								->getUsedProducts(null,$product);

				foreach($childProducts as $child) {
					if($this->attributesExist($child, $sizeCode)){
						$obj->children = "Yes";
						break;
					}
				}
				if($obj->children == "No"){
					$valid = false;
				}

				
				if($this->attributesExist($product, "manufacturer") && $product->getAttributeText('manufacturer') !=""){
					$obj->product_brand = $product->getAttributeText("manufacturer");
				}else{
					$obj->product_brand = "No";
					$valid = false;
				}
				
				$obj->valid = $valid;
				array_push($allProducts, $obj);
			}

			return $allProducts;
		}
		
	}
