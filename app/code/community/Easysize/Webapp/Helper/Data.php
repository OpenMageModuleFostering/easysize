<?php	


class Easysize_Webapp_Helper_Data extends Mage_Core_Helper_Abstract{


	public function isAvailable($id){

			
		   $current_product=Mage::getModel('catalog/product')->load($id); 			

			$sizeCode = Mage::getStoreConfig('easysize_section/easysize_group/size_field',Mage::app()->getStore());
			$genderCode = Mage::getStoreConfig('easysize_section/easysize_group/gender_field',Mage::app()->getStore());


		    if(!$current_product->isConfigurable() || !$this->attributesExist($current_product, $genderCode)) return false;

		    $product_brand = $current_product->getAttributeText('manufacturer');
		    
		    $ncat =  $current_product->getCategoryIds()[0];
		    $product_type =  Mage::getModel('catalog/category')->load($ncat)->getName();

		    $product_gender = $current_product->getAttributeText($genderCode);

		    $shop_id = Mage::getStoreConfig('easysize_section/easysize_group/id_shop_field',Mage::app()->getStore());

			$attributes = $current_product->getAttributes();
			$childProducts = Mage::getModel('catalog/product_type_configurable')
			                    ->getUsedProducts(null,$current_product);

			$sizeObj = array();
			foreach($childProducts as $child) {
				if($this->attributesExist($child, $sizeCode)){
					$chid = $child->getId();
					$current_child_product=Mage::getModel('catalog/product')->load($chid);
					$qty =  $current_child_product->getStockItem()->getQty();
					$size =  $current_child_product->getAttributeText($sizeCode); 
					$sizeObj[$size] = $qty;
				}
			}

			if(!empty($izeObj) || !$product_brand || !$product_gender || !$product_type){
				return false;
			}else{
				return true;
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
	}

	

?>