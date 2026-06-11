<?php
namespace App\Policies;

use App\Models\User;
use App\Models\OrdenPago;

class OrdenPagoPolicy
{
    public function view(User $user, OrdenPago $ordenPago)
    {
        // Tracking universal: todos los usuarios autenticados pueden ver el tracking
        // Las restricciones de acción (actualizar, aprobar, etc.) se manejan en métodos separados
        return true;
    }
    
    public function create(User $user)
    {
        return $user->hasRole('Tesorería');
    }
    
    public function update(User $user, OrdenPago $ordenPago)
    {
        $role = $user->roles->first()->name ?? null;
        if ($role === 'Tesorería' && in_array($ordenPago->estado, ['pendiente_tesoreria', 'rechazado_financiera'])) return true;
        if ($role === 'Financiera' && in_array($ordenPago->estado, ['rechazado_contabilidad', 'rechazado_administracion'])) return true;
        return false;
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
        if ($role === 'Contabilidad' && $ordenPago->estado === 'rechazado_presupuesto') return true;
        if ($role === 'Caja' && in_array($ordenPago->estado, ['cheque_generado', 'en_caja'])) return true;
        if ($role === 'Super Admin' && $ordenPago->estado === 'entregado') return true;
        if ($role === 'Financiera' && in_array($ordenPago->estado, ['enviado_financiera', 'enviado_financiera_cheque', 'rechazado_contabilidad', 'rechazado_administracion'])) return true;
        if ($role === 'Presupuesto' && $ordenPago->estado === 'rechazado_financiera_cheque') return true;
        if ($role === 'Tesorería' && $ordenPago->estado === 'rechazado_financiera') return true;
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