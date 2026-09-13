<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Vehiculo extends Model
{
    protected $table = 'vehiculo';
    protected $primaryKey = 'placa_vehiculo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
 
    protected $fillable = [
        'placa_vehiculo',
        'no_documento_cliente',
        'id_tipo_vehiculo',
        'color_vehiculo',
        'marca_vehiculo',
        'modelo_vehiculo',
        'estado_vehiculo',
    ];
 
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'no_documento_cliente', 'no_documento_cliente');
    }
 
    public function tipoVehiculo()
    {
        return $this->belongsTo(tipovehiculo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }
 
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'placa_vehiculo', 'placa_vehiculo');
    }
}
 