<?php

namespace Atomjoy\Apilogin\Contracts;

trait HasRolesPermissions
{
	/**
	 * Spatie roles with permissions
	 */
	public function roles_permissions()
	{
		return $this->roles()->with(['permissions' => function ($q) {
			$q->select('id', 'name', 'guard_name');
		}]);
	}
}
