<?php
namespace Ktpl\Sales\Model\Quote;
class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\SalesRule\Model\Validator $validator, \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency)
    {
        $this->setCode('seperate_discount');
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }

    public function collect(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()
            ->getAddress();

            $discount_value_1 = \Magento\Framework\App\ObjectManager::getInstance()
			    ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
			    ->getValue(
			        'ktpllabs/general/discount_rate',
			        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			    );

			$discount_value_2 = \Magento\Framework\App\ObjectManager::getInstance()
			    ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
			    ->getValue(
			        'ktpllabs/general/discount_rate_2',
			        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			    );

			$discount_value_3 = \Magento\Framework\App\ObjectManager::getInstance()
			    ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
			    ->getValue(
			        'ktpllabs/general/discount_rate_3',
			        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			    );

			 $qty_1 = \Magento\Framework\App\ObjectManager::getInstance()
			    ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
			    ->getValue(
			        'ktpllabs/general/product_qty_1',
			        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			    );
			 
			 $qty_2 = \Magento\Framework\App\ObjectManager::getInstance()
			    ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
			    ->getValue(
			        'ktpllabs/general/product_qty_2',
			        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			    );        

			 $qty_3 = \Magento\Framework\App\ObjectManager::getInstance()
			    ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
			    ->getValue(
			        'ktpllabs/general/product_qty_3',
			        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			    );

	    //$qty = $quote->getItemsQty();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
		 
		// get quote items collection
		$itemsCollection = $cart->getQuote()->getItemsCollection();
		 
		// get array of all items what can be display directly
		$itemsVisible = $cart->getQuote()->getAllVisibleItems();
		 
		// get quote items array
		$items = $cart->getQuote()->getAllItems();
		$calc=0;
		$TotalAmount=0;
		$tmp=0;
		foreach($items as $item) {
		    $id = $item->getProductId();		   		  
		    $quantity = $item->getQty();
		    $price = $item->getPrice();	
		    if($quantity == $qty_1){
		    	$subtotal = $quantity*$price;
		    	$calc = $subtotal*$discount_value_1 / 100;
		    }
		    if($quantity == $qty_2){
		    	$subtotal = $quantity*$price;
		    	$calc = $subtotal*$discount_value_2 / 100;
		    }
		    if($quantity >= $qty_3){
		    	$subtotal = $quantity*$price;
		    	$calc = $subtotal*$discount_value_3 / 100;
		    }

		    $tmp = $tmp+$calc;
		} 		
		
		$TotalAmount = $TotalAmount + $tmp;
	    
         $label = 'My Custom Discount';
         //$TotalAmount = $total->getSubtotal();
         //$TotalAmount = $TotalAmount / 100; //Set 10% discount         
        $discountAmount = "-" . $TotalAmount;
        $appliedCartDiscount = 0;

        if ($total->getDiscountDescription())
        {
            $appliedCartDiscount = $total->getDiscountAmount();
            $discountAmount = $total->getDiscountAmount() + $discountAmount;
            $label = $total->getDiscountDescription() . ', ' . $label;
        }

        $total->setDiscountDescription($label);
        $total->setDiscountAmount($discountAmount);
        $total->setBaseDiscountAmount($discountAmount);
        $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);

        if (isset($appliedCartDiscount))
        {
            $total->addTotalAmount($this->getCode() , $discountAmount - $appliedCartDiscount);
            $total->addBaseTotalAmount($this->getCode() , $discountAmount - $appliedCartDiscount);
        }
        else
        {
            $total->addTotalAmount($this->getCode() , $discountAmount);
            $total->addBaseTotalAmount($this->getCode() , $discountAmount);
        }
        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0)
        {
            $description = $total->getDiscountDescription();
            $result = ['code' => $this->getCode() , 'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount') , 'value' => $amount];
        }
        return $result;
    }
}

