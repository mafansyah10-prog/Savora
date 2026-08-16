<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SavoraPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear settings cache for testing
        Cache::forget('global_settings');

        Setting::create([
            'store_name' => 'Savora Test',
            'whatsapp_number' => '6289601905406',
            'instagram_url' => 'https://instagram.com/savora',
            'store_address' => 'Jl. Test No. 123',
            'hero_title' => 'Savora Test Title',
            'hero_subtitle' => 'Savora Test Subtitle',
            'about_text' => 'About test text',
        ]);
    }

    public function test_home_page_can_be_rendered()
    {
        // Force settings database check
        $global = Setting::getGlobal();

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee($global->store_name);
    }

    public function test_categories_index_page_can_be_rendered()
    {
        $category = Category::create([
            'name' => 'Gourmet',
            'slug' => 'gourmet',
            'icon' => 'utensils',
            'description' => 'Test category description',
        ]);

        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Gourmet');
    }

    public function test_product_detail_page_can_be_rendered()
    {
        $category = Category::create([
            'name' => 'Gourmet',
            'slug' => 'gourmet',
            'icon' => 'utensils',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Salmon Bowl',
            'slug' => 'salmon-bowl',
            'price' => 150000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->get(route('product.show', 'salmon-bowl'));
        $response->assertStatus(200);
        $response->assertSee('Salmon Bowl');
        $response->assertSee('Rp 150.000');
    }

    public function test_add_product_to_cart_and_modify_quantity()
    {
        $category = Category::create([
            'name' => 'Gourmet',
            'slug' => 'gourmet',
            'icon' => 'utensils',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Salmon Bowl',
            'slug' => 'salmon-bowl',
            'price' => 150000,
            'stock' => 5,
            'is_active' => true,
        ]);

        // 1. Add to cart
        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $this->assertTrue(session()->has('cart'));
        $this->assertEquals(1, session('cart')[$product->id]['quantity']);

        // 2. Update quantity
        $response = $this->post(route('cart.update'), [
            'id' => $product->id,
            'quantity' => 3,
        ]);
        $response->assertRedirect();
        $this->assertEquals(3, session('cart')[$product->id]['quantity']);

        // 3. Remove from cart
        $response = $this->post(route('cart.remove'), [
            'id' => $product->id,
        ]);
        $response->assertRedirect();
        $this->assertEmpty(session('cart'));
    }

    public function test_checkout_saves_order_and_increments_sales_count()
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Gourmet',
            'slug' => 'gourmet',
            'icon' => 'utensils',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Salmon Bowl',
            'slug' => 'salmon-bowl',
            'price' => 150000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Place item in session cart first
        session()->put('cart', [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
                'image' => null,
                'stock' => 10,
                'category' => 'Gourmet',
            ],
        ]);

        $response = $this->actingAs($user)->followingRedirects()->post(route('cart.checkout'), [
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Merdeka No. 45',
            'payment_method' => 'e_wallet',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('cart.success');

        // Check if database has the order recorded
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
            'total_amount' => 300000,
            'status' => 'pending',
            'payment_method' => 'e_wallet',
        ]);

        // Check sales_count incremented
        $product->refresh();
        $this->assertEquals(2, $product->sales_count);
    }

    public function test_pakasir_webhook_successfully_updates_order_status()
    {
        $setting = Setting::first();
        $setting->update([
            'pakasir_project' => 'my-test-project',
            'pakasir_api_key' => 'secret-api-key',
            'pakasir_is_active' => true,
        ]);
        Cache::forget('global_settings');

        $user = User::create([
            'name' => 'John Webhook',
            'email' => 'webhook@test.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Gourmet',
            'slug' => 'gourmet',
            'icon' => 'utensils',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Salmon Bowl',
            'slug' => 'salmon-bowl',
            'price' => 150000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Webhook',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test Webhook',
            'total_amount' => 150000,
            'status' => 'pending',
            'payment_method' => 'pakasir',
            'items' => [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 2,
                    'price' => 75000,
                ],
            ],
        ]);

        $response = $this->postJson('/webhook/pakasir', [
            'project' => 'my-test-project',
            'order_id' => $order->id,
            'amount' => 150000,
            'status' => 'completed',
            'payment_method' => 'qris',
            'completed_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $product->refresh();
        $this->assertEquals(8, $product->stock);
        $this->assertTrue($order->stock_deducted);
    }

    public function test_voucher_requires_shopping_amount_higher_than_discount_value()
    {
        $voucher = Voucher::create([
            'code' => 'BIGDISCOUNT',
            'type' => 'fixed',
            'value' => 50000,
            'min_order_amount' => 10000,
            'is_active' => true,
            'is_hidden' => false,
        ]);

        // Case 1: Shopping amount is 40.000 (less than 50.000 voucher discount value) -> should fail
        $subtotal = 40000;
        $this->assertFalse($voucher->isValidForAmount($subtotal));

        // Case 2: Shopping amount is 50.000 (equal to 50.000 voucher discount value) -> should fail
        $subtotal = 50000;
        $this->assertFalse($voucher->isValidForAmount($subtotal));

        // Case 3: Shopping amount is 60.000 (greater than 50.000 voucher discount value) -> should pass
        $subtotal = 60000;
        $this->assertTrue($voucher->isValidForAmount($subtotal));
    }

    public function test_midtrans_webhook_successfully_updates_order_status()
    {
        $setting = Setting::first();
        $setting->update([
            'midtrans_server_key' => 'test-server-key',
            'midtrans_is_active' => true,
        ]);
        Cache::forget('global_settings');

        $user = User::create([
            'name' => 'John Midtrans',
            'email' => 'midtrans@test.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Dessert',
            'slug' => 'dessert',
            'icon' => 'cake',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chocolate Lava',
            'slug' => 'chocolate-lava',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Midtrans',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test Midtrans',
            'total_amount' => 50000,
            'status' => 'pending',
            'payment_method' => 'midtrans',
            'items' => [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 1,
                    'price' => 50000,
                ],
            ],
        ]);

        // Calculate signature key: order_id + status_code + gross_amount + serverKey
        $statusCode = '200';
        $grossAmount = '50000.00';
        $serverKey = 'test-server-key';
        $signatureKey = hash('sha512', $order->id.$statusCode.$grossAmount.$serverKey);

        $response = $this->postJson('/webhook/midtrans', [
            'order_id' => $order->id,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-tx-12345',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $product->refresh();
        $this->assertEquals(9, $product->stock);
        $this->assertTrue($order->stock_deducted);
    }

    public function test_manual_payment_is_active_overrides_pakasir_and_renders_manual_details()
    {
        $setting = Setting::first();
        $setting->update([
            'pakasir_is_active' => true,
            'manual_payment_is_active' => true,
            'manual_payment_methods' => [
                [
                    'name' => 'BCA Test',
                    'account_number' => '9876543210',
                    'account_name' => 'Savora Test Manual',
                    'qris_image' => null,
                ],
            ],
        ]);
        Cache::forget('global_settings');

        $user = User::create([
            'name' => 'John Manual',
            'email' => 'manual@test.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Manual',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test Manual',
            'total_amount' => 25000,
            'status' => 'pending',
            'payment_method' => 'bca',
            'items' => [],
        ]);

        $response = $this->actingAs($user)->get(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('Instruksi Pembayaran Manual');
        $response->assertSee('BCA Test');
        $response->assertSee('9876543210');
        $response->assertSee('Savora Test Manual');
        // Make sure it doesn't render Pakasir button when manual_payment_is_active is true
        $response->assertDontSee('Menunggu Pembayaran Pakasir');
    }

    public function test_customer_can_upload_delivery_proof_to_complete_shipped_order()
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'John Receiver',
            'email' => 'receiver@test.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Receiver',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Test Receiver',
            'total_amount' => 25000,
            'status' => 'shipped',
            'payment_method' => 'bca',
            'items' => [],
        ]);

        $response = $this->actingAs($user)->get(route('orders.show', $order));
        $response->assertStatus(200);
        $response->assertSee('Konfirmasi Produk Sampai');

        $file = UploadedFile::fake()->image('delivery_proof.jpg');

        $response = $this->actingAs($user)->post(route('orders.upload_delivery_proof', $order), [
            'delivery_proof' => $file,
        ]);

        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->delivery_proof);
        Storage::disk('public')->assertExists($order->delivery_proof);
    }
}
