<?php

class Product {
    public $code;
    public $price;

    public function __construct($code, $price) {
        $this->code = $code;
        $this->price = $price;
    }
}

class Basket {
    private $products = [];
    private $basket = [];
    private $deliveryRules = [];
    private $offers = [];

    public function __construct($products, $deliveryRules, $offers) {
        $this->products = $products;
        $this->deliveryRules = $deliveryRules;
        $this->offers = $offers;
    }

    public function add($productCode) {
        if (!isset($this->products[$productCode])) {
            throw new Exception("Product code $productCode not found.");
        }
        $this->basket[] = $this->products[$productCode];
    }

    public function total() {
        $total = 0.0;
        $productCount = [];

        foreach ($this->basket as $product) {
            $total += $product->price;
            if (!isset($productCount[$product->code])) {
                $productCount[$product->code] = 0;
            }
            $productCount[$product->code]++;
        }

        // Apply offers
        foreach ($this->offers as $offer) {
            $total = $offer->apply($productCount, $total);
        }

        // Apply delivery charges
        $deliveryCost = $this->calculateDeliveryCost($total);
        $total += $deliveryCost;

        return number_format($total, 2);
    }

    private function calculateDeliveryCost($total) {
        foreach ($this->deliveryRules as $threshold => $cost) {
            if ($total < $threshold) {
                return $cost;
            }
        }
        return 0.0;
    }
}

class Offer {
    private $productCode;
    private $quantity;
    private $discountPercentage;

    public function __construct($productCode, $quantity, $discountPercentage) {
        $this->productCode = $productCode;
        $this->quantity = $quantity;
        $this->discountPercentage = $discountPercentage;
    }

    public function apply(&$productCount, $total) {
        if (isset($productCount[$this->productCode]) && $productCount[$this->productCode] >= $this->quantity) {
            $discountedItems = intdiv($productCount[$this->productCode], $this->quantity);
            $productPrice = $total / $productCount[$this->productCode];
            $discount = $productPrice * $this->discountPercentage * $discountedItems;
            $total -= $discount;
        }
        return $total;
    }
}

// Initialize products
$products = [
    'R01' => new Product('R01', 32.95),
    'G01' => new Product('G01', 24.95),
    'B01' => new Product('B01', 7.95),
];

// Initialize delivery rules
$deliveryRules = [
    50 => 4.95,
    90 => 2.95,
    1000 => 0.00, // A high threshold to ensure free delivery above $90
];

// Initialize offers
$offers = [
    new Offer('R01', 2, 0.5), // Buy 1 get 2nd half price
];

// Create basket and add products
$basket = new Basket($products, $deliveryRules, $offers);
$basket->add('B01');
$basket->add('B01');
$basket->add('R01');
$basket->add('R01');
$basket->add('R01');

// Print total
echo "Total: $" . $basket->total();

