<?php

namespace Atomjoy\Apilogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Atomjoy\Apilogin\Exceptions\JsonException;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificationsController extends Controller
{
	function index($page)
	{
		try {
			$perpage = config('apilogin.notifications_perpage', 6);
			$page = (int) $page;
			if ($page < 1) $page = 1;
			$offset = ($page - 1) * $perpage;
			if ($offset < 0) $offset = 0;

			$list = Auth::user()->notifications()
				->latest()
				->offset($offset)
				->limit($perpage)->get()->each(function ($n) {
					$n->formatted_created_at = $n->created_at->format('Y-m-d H:i:s');
				});

			$unread = Auth::user()->unreadNotifications()->count();

			return response()->json([
				'message' => 'Notifications.',
				'notifications' => $list,
				'unread' => $unread,
			]);
		} catch (Exception $e) {
			report($e);
			throw new JsonException(__('apilogin.notifications.error'), 422);
		}
	}

	function toggle($id)
	{
		try {
			$item = Auth::user()->notifications()->find($id);

			if ($item != null) {
				$item->read_at != null ? $item->markAsUnread() : $item->markAsRead();
			}

			return response()->json([
				'message' => __('apilogin.notifications.success')
			]);
		} catch (Exception $e) {
			report($e);
			throw new JsonException(__('apilogin.notifications.error'), 422);
		}
	}

	function readall()
	{
		try {
			Auth::user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

			// Auth::user()->unreadNotifications()->update(['read_at' => now()]);
			// Auth::user()->unreadNotifications->map(function ($n) { $n->markAsRead(); });

			return response()->json([
				'message' => __('apilogin.notifications.success')
			]);
		} catch (Exception $e) {
			report($e);
			throw new JsonException(__('apilogin.notifications.error'), 422);
		}
	}
}
