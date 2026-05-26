<?php
namespace App\Policies;

use App\Models\User;
use App\Models\OrdenPago;

class OrdenPagoPolicy
{
    public function view(User $user, OrdenPago $ordenPago)
    {
        $role = $user->roles->first()->name ?? null;
        
        if ($role === 'Super Admin') return true;
        if ($role === 'Archivos') return true;
        if ($role === 'Tesorería' && in_array($ordenPago->estado, ['pendiente_tesoreria', 'rechazado_financiera'])) return true;
        if ($role === 'Financiera') return true;
        if ($role === 'Contabilidad') return true;
        if ($role === 'Presupuesto') return true;
        if ($role === 'Administración') return true;
        if ($role === 'Caja') return true;
        
        return $user->id === $ordenPago->creado_por;
    }
    
    public function create(User $user)
    {
        return $user->hasRole('Tesorería');
    }
    
    public function update(User $user, OrdenPago $ordenPago)
    {
        $role = $user->roles->first()->name ?? null;
        return $role === 'Tesorería' && in_array($ordenPago->estado, ['pendiente_tesoreria', 'rechazado_financiera']);
    }
    
    public function delete(User $user, OrdenPago $ordenPago)
    {
        return $user->hasRole('Super Admin') && $ordenPago->estado === 'pendiente_tesoreria';
    }
    
    public function aprobar(User $user, OrdenPago $ordenPago)
    {
        return $user->hasRole('Tesorería') && in_array($ordenPago->estado, ['pendiente_tesoreria', 'rechazado_financiera']);
    }
    
    public function generarCheque(User $user, OrdenPago $ordenPago)
    {
        return $user->hasRole('Contabilidad') && $ordenPago->estado === 'enviado_contabilidad';
    }

    public function actualizar(User $user, OrdenPago $ordenPago)
    {
        $role = $user->roles->first()->name ?? null;
        if ($role === 'Contabilidad' && $ordenPago->estado === 'cheque_generado') return true;
        if ($role === 'Caja' && in_array($ordenPago->estado, ['cheque_generado', 'en_caja'])) return true;
        if ($role === 'Super Admin' && $ordenPago->estado === 'entregado') return true;
        return false;
    }
    
    public function entregar(User $user, OrdenPago $ordenPago)
    {
        return $user->hasRole('Caja') && in_array($ordenPago->estado, ['cheque_generado', 'en_caja']);
    }
    
    public function cerrar(User $user, OrdenPago $ordenPago)
    {
        return $user->hasRole('Super Admin') && $ordenPago->estado === 'entregado';
    }
}