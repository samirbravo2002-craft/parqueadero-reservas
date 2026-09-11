<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Cliente extends Model
{
    protected $table = 'cliente';
    protected $primaryKey = 'no_documento_cliente';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
 
    protected $fillable = [
        'no_documento_cliente',
        'id_usuario',
    ];
 
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
 
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'no_documento_cliente', 'no_documento_cliente');
    }
 
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'no_documento_cliente', 'no_documento_cliente');
    }
}
 