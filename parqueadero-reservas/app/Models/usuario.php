<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;
 
    protected $fillable = [
        'tipo_documento',
        'nombre_usuario',
        'apellido_usuario',
        'numero_celular',
        'id_rol',
        'contrasenia',
        'estado_usuario',
    ];
 
    protected $hidden = [
        'contrasenia',
    ];
 
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
 
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_usuario', 'id_usuario');
    }
 
    public function administrador()
    {
        return $this->hasOne(administrador::class, 'id_usuario', 'id_usuario');
    }
}
 