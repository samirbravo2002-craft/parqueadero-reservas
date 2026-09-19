<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    // MODIFICADO: ahora existen 3 estados posibles. 3 = cancelada (nuevo).
    // Una reserva cancelada, igual que una finalizada, ya no cuenta para
    // el cupo del día (ver scopeEnProceso) y NUNCA se elimina físicamente:
    // se conserva para auditoría/políticas de la empresa.
    public const ESTADO_EN_PROCESO = 1;
    public const ESTADO_FINALIZADA = 2;
    public const ESTADO_CANCELADA = 3; // NUEVO

    protected $table = 'reserva';
    protected $primaryKey = 'id_reserva';
    public $timestamps = false;

    protected $fillable = [
        'no_documento_cliente',
        'placa_vehiculo',
        'no_documento_administrador',
        'fecha',
        'hora',
        'id_servicio',
        'id_tipo_vehiculo',
        'estado_reserva', // 1 = en proceso, 2 = finalizada, 3 = cancelada (NUEVO)
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'no_documento_cliente', 'no_documento_cliente');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'placa_vehiculo', 'placa_vehiculo');
    }

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'no_documento_administrador', 'no_documento_administrador');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function tipoVehiculo()
    {
        return $this->belongsTo(tipovehiculo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }

    public function comprobantePago()
    {
        return $this->hasOne(ComprobantePago::class, 'id_reserva', 'id_reserva');
    }

    /**
     * NUEVO: reservas que todavía ocupan cupo (no han sido atendidas/finalizadas).
     */
    public function scopeEnProceso(Builder $query): Builder
    {
        return $query->where('estado_reserva', 1);
    }

    /**
     * NUEVO: reservas ya atendidas, que liberaron su cupo.
     */
    public function scopeFinalizadas(Builder $query): Builder
    {
        return $query->where('estado_reserva', 2);
    }

    /**
     * NUEVO: reservas canceladas por el cliente. También liberaron su cupo,
     * pero se conservan (nunca se borran) para consulta/auditoría.
     */
    public function scopeCanceladas(Builder $query): Builder
    {
        return $query->where('estado_reserva', self::ESTADO_CANCELADA);
    }

    /**
     * NUEVO: reservas que ya no están activas (finalizadas o canceladas),
     * usado por el panel de Control para el historial por políticas de
     * conservación de registros.
     */
    public function scopeHistorial(Builder $query): Builder
    {
        return $query->whereIn('estado_reserva', [self::ESTADO_FINALIZADA, self::ESTADO_CANCELADA]);
    }

    /**
     * NUEVO: texto legible del estado, usado en las vistas (mi-cuenta, voucher).
     */
    public function getEstadoTextoAttribute(): string
    {
        return match ((int) $this->estado_reserva) {
            self::ESTADO_FINALIZADA => 'Finalizada',
            self::ESTADO_CANCELADA => 'Cancelada',
            default => 'En proceso',
        };
    }
}