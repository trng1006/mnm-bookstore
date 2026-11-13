<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompletenessFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_crud_coupons_and_customer_is_forbidden(): void
    {
        $admin = User::create([
            'name' => 'Admin Coupon',
            'email' => 'admin-coupon@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $customer = User::create([
            'name' => 'Customer Coupon',
            'email' => 'customer-coupon@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.coupons.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => '  sale20  ',
            'type' => 'percent',
            'value' => 20,
            'min_order_value' => 100000,
            'usage_limit' => 50,
            'is_active' => '1',
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::where('code', 'SALE20')->firstOrFail();
        $this->assertSame('percent', $coupon->type);
        $this->assertSame(20, $coupon->value);
        $this->assertTrue($coupon->is_active);

        $this->actingAs($admin)->put(route('admin.coupons.update', $coupon), [
            'code' => 'SALE20',
            'type' => 'fixed',
            'value' => 25000,
            'min_order_value' => 150000,
            'usage_limit' => 10,
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon->refresh();
        $this->assertSame('fixed', $coupon->type);
        $this->assertSame(25000, $coupon->value);
        $this->assertFalse($coupon->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.coupons.destroy', $coupon))
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_customer_can_change_password_and_rejects_wrong_current_password(): void
    {
        $user = User::create([
            'name' => 'Reader User',
            'email' => 'reader-pass@example.com',
            'password' => Hash::make('oldpassword123'),
            'role' => 'customer',
        ]);

        $this->actingAs($user)->post(route('account.profile.password'), [
            'current_password' => 'wrongpass',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('oldpassword123', $user->fresh()->password));

        $this->actingAs($user)->post(route('account.profile.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect()->assertSessionHas('password_success');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_support_and_policy_pages_render_successfully(): void
    {
        $this->get(route('pages.support'))
            ->assertOk()
            ->assertSee('Vận chuyển & Giao nhận', false)
            ->assertSee('Đổi trả & Hoàn tiền', false)
            ->assertSee('Câu hỏi thường gặp');

        $this->get(route('pages.policy'))
            ->assertOk()
            ->assertSee('Chính sách bảo mật thông tin')
            ->assertSee('Điều khoản dịch vụ');
    }
}
