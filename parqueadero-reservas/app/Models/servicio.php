<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Servicio extends Model
{
    protected $table = 'servicio';
    protected $primaryKey = 'id_servicio';
    public $timestamps = false;
 
    protected $fillable = [
        'nombre_servicio',
        'id_tipo_vehiculo',
        'descripcion_servicio',
        'costo_servicio',
        'estado_servicio',
    ];
 
    public function tipoVehiculo()
    {
        return $this->belongsTo(tipovehiculo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }
 
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_servicio', 'id_servicio');
    }
}
 