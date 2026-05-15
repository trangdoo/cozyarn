<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index(Request $request)
    {
        $filters = [
            'q'      => trim((string) $request->query('q', '')),
            'role'   => $request->query('role', 'all'),
            'status' => $request->query('status', 'all'),
        ];

        return view('admin.users.index', [
            'users'  => $this->users->paginate($filters, 15),
            'filter' => $filters,
            'stats'  => $this->users->stats(),
        ]);
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => null,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->users->create($request->validated());

        return redirect()
            ->route('admin.users.show', $user)
            ->with('cart_flash', "Đã tạo tài khoản {$user->name}.");
    }

    public function show(User $user)
    {
        // Lấy đơn hàng của user từ session (legacy demo). Khi migrate Order sang DB:
        //   $orders = Order::where('user_id', $user->id)->latest()->get()->toArray();
        $orders = array_values(array_filter(session('orders', []), fn ($o) =>
            ($o['user_id'] ?? null) === $user->id
        ));
        usort($orders, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $risk = $this->users->computeRisk($user, $orders);

        return view('admin.users.show', [
            'user'        => $user,
            'orders'      => $orders,
            'stats'       => [
                'total'          => \count($orders),
                'active'         => $risk['bucket']['active'],
                'received'       => $risk['bucket']['received'],
                'cancelled'      => $risk['bucket']['cancelled'],
                'returned'       => $risk['bucket']['returned'],
                'returnReq'      => $risk['bucket']['return_requested'],
                'cancelRatio'    => $risk['totals']['cancelRatio'],
                'returnRatio'    => $risk['totals']['returnRatio'],
                'totalSpent'     => $risk['totals']['spent'],
                'totalItems'     => $risk['totals']['items'],
                'accountAgeDays' => $risk['totals']['accountAgeDays'],
            ],
            'risk'        => $risk['score'],
            'riskLevel'   => $risk['level'],
            'riskReasons' => $risk['reasons'],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->users->adminUpdate($user, $request->validated());

        return back()->with('cart_flash', 'Đã cập nhật tài khoản.');
    }

    public function destroy(User $user)
    {
        try {
            $this->users->delete($user, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('cart_flash', $e->getMessage());
        }
        return redirect()->route('admin.users.index')->with('cart_flash', 'Đã xoá tài khoản.');
    }

    public function toggleBlock(User $user)
    {
        try {
            $user = $this->users->toggleBlock($user, auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('cart_flash', $e->getMessage());
        }
        return back()->with('cart_flash', $user->status === 'blocked'
            ? 'Đã khoá tài khoản.'
            : 'Đã mở khoá tài khoản.');
    }
}
