<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Web\Admin\Traits\User;

use App\Models\Permission;
use App\Models\Role;
use App\Http\Requests\Admin\Request;

trait HasAdminUser
{
	/**
	 * Set admin flag when roles contain staff permissions
	 *
	 * @param \App\Http\Requests\Admin\Request $request
	 * @return \App\Http\Requests\Admin\Request
	 */
	protected function handleIsAdminFromRoles(Request $request)
	{
		$isFilled = false;
		
		if ($request->filled('roles')) {
			$rolesIds = (array)$request->input('roles');
			$hasSuperAdminOrStaffRole = false;
			$hasBotRole = false;

			foreach ($rolesIds as $roleId) {
				$role = is_numeric($roleId) ? Role::find($roleId) : Role::where('name', $roleId)->first();
				$roleName = !empty($role) ? strtolower(trim($role->name)) : strtolower(trim($roleId));

				if ($roleName === strtolower(Role::getSuperAdminRole())) {
					$hasSuperAdminOrStaffRole = true;
				}
				if ($roleName === 'bot') {
					$hasBotRole = true;
				}

				if (!empty($role)) {
					$permissions = $role->permissions;
					if ($permissions->count() > 0) {
						foreach ($permissions as $permission) {
							if (in_array($permission->name, Permission::getStaffPermissions())) {
								$hasSuperAdminOrStaffRole = true;
							}
						}
					}
				}
			}

			// Se a role 'bot' for selecionada sem super-admin, desativa is_admin
			if ($hasBotRole && !$hasSuperAdminOrStaffRole) {
				$request->request->set('is_admin', 0);
				return $request;
			}

			if ($hasSuperAdminOrStaffRole) {
				$isFilled = true;
			}
		}
		
		if ($request->has('is_admin') && ((int)$request->input('is_admin') === 1 || $request->input('is_admin') === '1' || $request->input('is_admin') === true)) {
			$isFilled = true;
		}

		if ($isFilled) {
			$request->request->set('is_admin', 1);
			
			// Se o formulário enviou a lista de roles (pivot sync do Backpack), garante que a role super-admin esteja inclusa
			if ($request->has('roles')) {
				$superAdminRole = Role::ensureSuperAdminRoleExists();
				if (!empty($superAdminRole)) {
					$roles = (array)$request->input('roles', []);
					if (!in_array($superAdminRole->id, $roles) && !in_array((string)$superAdminRole->id, $roles) && !in_array($superAdminRole->name, $roles)) {
						$roles[] = $superAdminRole->id;
						$request->request->set('roles', $roles);
					}
				}
			}
		} else {
			$request->request->set('is_admin', 0);
		}
		
		return $request;
	}
	
	/**
	 * Set admin flag when staff permissions are selected
	 *
	 * @param \App\Http\Requests\Admin\Request $request
	 * @return \App\Http\Requests\Admin\Request
	 */
	protected function handleIsAdminFromPermissions(Request $request)
	{
		$isFilled = false;
		
		if ($request->has('is_admin') && ((int)$request->input('is_admin') === 1 || $request->input('is_admin') === '1' || $request->input('is_admin') === true)) {
			$isFilled = true;
		}
		
		if ($request->filled('permissions')) {
			$permissionIds = (array)$request->input('permissions');
			foreach ($permissionIds as $permissionId) {
				$permission = Permission::find($permissionId);
				if (!empty($permission) && in_array($permission->name, Permission::getStaffPermissions())) {
					$isFilled = true;
				}
			}
		}
		
		if ($isFilled) {
			$request->request->set('is_admin', 1);
		} else if ($request->has('is_admin') && (int)$request->input('is_admin') === 0) {
			$request->request->set('is_admin', 0);
		}
		
		return $request;
	}
}
