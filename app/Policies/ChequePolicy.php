<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Cheque;

class ChequePolicy
{
    public function view(User $user, Cheque $cheque)
    {
        $role = $user->roles->first()->name;
        
        if ($role === 'Super Admin') return true;
        if ($role === 'Contabilidad') return true;
        if ($role === 'Caja' && $cheque->estado !== 'anulado') return true;
        
        return false;
    }
    
    public function create(User $user)
    {
        return $user->hasRole('Contabilidad');
    }
    
    public function update(User $user, Cheque $cheque)
    {
        return $user->hasRole('Super Admin') && $cheque->estado === 'emitido';
    }
    
    public function delete(User $user, Cheque $cheque)
    {
        return false;
    }
    
    public function imprimir(User $user, Cheque $cheque)
    {
        $role = $user->roles->first()->name;
        return in_array($role, ['Contabilidad', 'Super Admin']);
    }
    
    public function anular(User $user, Cheque $cheque)
    {
        $role = $user->roles->first()->name;
        return in_array($role, ['Contabilidad', 'Super Admin']) && in_array($cheque->estado, ['emitido', 'impreso']);
    }
}