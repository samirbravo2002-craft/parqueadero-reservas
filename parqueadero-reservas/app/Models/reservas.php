<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Reserva extends Model
{
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
}
 