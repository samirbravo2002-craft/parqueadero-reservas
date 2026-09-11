<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class TipoVehiculo extends Model
{
    protected $table = 'tipo_vehiculo';
    protected $primaryKey = 'id_tipo_vehiculo';
    public $timestamps = false;
 
    protected $fillable = [
        'nombre_tipo_vehiculo',
        'estado_vehiculo',
    ];
 
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }
 
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'id_tipo_vehiculo', 'id_tipo_vehiculo');
    }
}