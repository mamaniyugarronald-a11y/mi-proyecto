<?php

namespace App\Http\Controllers;

use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Models\HistorialSesionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        if (session()->has('idusuario')) {
            return redirect()->route('dashboard');
        }
        return view('index');
    }

    public function validar(Request $request)
    {
        $request->validate([
            'usu' => 'required|string|max:50',
            'pwd' => 'required|string|max:255'
        ], [
            'usu.required' => 'El nombre de usuario es obligatorio.',
            'pwd.required' => 'La contraseña es obligatoria.'
        ]);

        $usuario = UsuarioModel::with('rol')->where('usuario', $request->usu)->first();

        // Las cuentas creadas antes de esta mejora guardaban texto plano. No se debe
        // enviarlo a Hash::check porque Laravel rechaza valores que no son Bcrypt.
        $esBcrypt = $usuario && preg_match('/^\$2[ayb]\$.{56}$/', (string) $usuario->password) === 1;
        $coincideHash = $esBcrypt && Hash::check($request->pwd, $usuario->password);
        $coincideLegado = $usuario && !$esBcrypt && hash_equals((string) $usuario->password, (string) $request->pwd);
        if (!$usuario || !($coincideHash || $coincideLegado)) {
            return redirect()->route('login')
                ->withInput()
                ->with('error_login', 'Usuario o contraseña incorrectos.');
        }
        if ((int) $usuario->estado !== 1) {
            return redirect()->route('login')
                ->withInput()
                ->with('error_login', 'Esta cuenta se encuentra desactivada. Comuníquese con el administrador.');
        }
        // Compatibilidad con cuentas antiguas: se actualizan al formato seguro al iniciar sesión.
        if ($coincideLegado || ($esBcrypt && Hash::needsRehash($usuario->password))) {
            $usuario->password = Hash::make($request->pwd);
            $usuario->save();
        }

        // Registrar inicio de sesión en historial (HU5)
        try {
            HistorialSesionModel::create([
                'id_usuario' => $usuario->id_usuario,
                'fecha_hora' => now()
            ]);
        } catch (\Exception $e) {
            // Continuar incluso si falla el log
        }

        // Guardar variables de sesión
        session([
            'idusuario'   => $usuario->id_usuario,
            'nom'         => $usuario->nombre,
            'apellido'    => $usuario->apellido,
            'usuario'     => $usuario->usuario,
            'email'       => $usuario->email,
            'rol'         => $usuario->id_rol,
            'nombre_rol'  => $usuario->rol ? $usuario->rol->nombre_rol : 'Usuario'
        ]);

        return redirect()->route('dashboard')->with('mensaje_bienvenida', '¡Bienvenido(a), ' . $usuario->nombre . '!');
    }

    public function cerrar()
    {
        session()->flush();
        return redirect()->route('inicio')->with('mensaje', 'Ha cerrado sesión exitosamente.');
    }

    // LISTAR USUARIOS
    public function usuarios()
    {
        abort_unless(session('rol') == 1, 403);
        $usuarios = UsuarioModel::with('rol')->get();
        $roles = RolModel::withCount('usuarios')->orderBy('nombre_rol')->get();

        return view('usuario', compact('usuarios', 'roles'));
    }

    // GUARDAR USUARIO
    public function guardar(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'usuario'  => 'required|string|max:50|unique:usuario,usuario',
            'email'    => 'required|email|max:150|unique:usuario,email',
            'password' => 'required|string|min:8|max:255|confirmed',
            'id_rol'   => 'required|exists:rol,id_rol'
        ], [
            'usuario.unique'  => 'El nombre de usuario ya está registrado en el sistema.',
            'email.unique'    => 'El correo electrónico ya está registrado en el sistema.',
            'id_rol.exists'   => 'El rol seleccionado no es válido.',
            'password.confirmed' => 'La contraseña y su repetición no coinciden.'
        ]);

        UsuarioModel::create([
            'nombre'   => $request->nombre,
            'apellido' => $request->apellido,
            'usuario'  => $request->usuario,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'id_rol'   => $request->id_rol,
            'estado'   => 1
        ]);

        return redirect()->back()->with('mensaje', 'Usuario registrado correctamente.');
    }

    // ACTUALIZAR USUARIO
    public function actualizar(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validate([
            'id_usuario' => 'required|exists:usuario,id_usuario',
            'nombre'     => 'required|string|max:100',
            'apellido'   => 'required|string|max:100',
            'usuario'    => 'required|string|max:50|unique:usuario,usuario,' . $request->id_usuario . ',id_usuario',
            'email'      => 'required|email|max:150|unique:usuario,email,' . $request->id_usuario . ',id_usuario',
            'id_rol'     => 'required|exists:rol,id_rol',
            'estado'     => 'required|boolean',
            'password'   => 'nullable|string|min:8|max:255|confirmed'
        ], [
            'password.confirmed' => 'La contraseña y su repetición no coinciden.'
        ]);

        $usuario = UsuarioModel::findOrFail($request->id_usuario);
        $usuario->nombre   = $request->nombre;
        $usuario->apellido = $request->apellido;
        $usuario->usuario  = $request->usuario;
        $usuario->email    = $request->email;
        $usuario->id_rol   = $request->id_rol;
        $usuario->estado   = $request->boolean('estado');

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return redirect()->back()->with('mensaje', 'Usuario actualizado correctamente.');
    }

    // ELIMINAR USUARIO
    public function eliminar($id)
    {
        abort_unless(session('rol') == 1, 403);
        if ($id == session('idusuario')) {
            return redirect()->back()->with('error', 'No puede eliminar su propio usuario activo.');
        }

        $usuario = UsuarioModel::findOrFail($id);
        $usuario->delete();

        return redirect()->back()->with('mensaje', 'Usuario eliminado correctamente.');
    }

    // GESTIÓN DE ROLES
    public function guardarRol(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validateWithBag('crearRol', [
            'nombre_rol' => 'required|string|max:100|unique:rol,nombre_rol',
        ], [
            'nombre_rol.required' => 'Debe ingresar el nombre del rol.',
            'nombre_rol.unique' => 'Este rol ya se encuentra registrado.',
        ]);

        RolModel::create(['nombre_rol' => trim($request->nombre_rol)]);
        return redirect()->route('usuarios')->with('mensaje', 'Rol registrado correctamente.');
    }

    public function actualizarRol(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validateWithBag('editarRol', [
            'id_rol' => 'required|exists:rol,id_rol',
            'nombre_rol' => 'required|string|max:100|unique:rol,nombre_rol,' . $request->id_rol . ',id_rol',
        ], [
            'nombre_rol.required' => 'Debe ingresar el nombre del rol.',
            'nombre_rol.unique' => 'Este rol ya se encuentra registrado.',
        ]);

        $rol = RolModel::findOrFail($request->id_rol);
        $rol->nombre_rol = trim($request->nombre_rol);
        $rol->save();

        return redirect()->route('usuarios')->with('mensaje', 'Rol actualizado correctamente.');
    }

    public function eliminarRol($id)
    {
        abort_unless(session('rol') == 1, 403);
        $rol = RolModel::withCount('usuarios')->findOrFail($id);
        if ($rol->usuarios_count > 0) {
            return redirect()->route('usuarios')->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $rol->delete();
        return redirect()->route('usuarios')->with('mensaje', 'Rol eliminado correctamente.');
    }

    // HU5: AUDITORÍA DE ACCESOS Y SESIONES
    public function auditoria()
    {
        $sesiones = HistorialSesionModel::with('usuario.rol')
            ->orderBy('fecha_hora', 'desc')
            ->take(100)
            ->get();

        return view('auditoria', compact('sesiones'));
    }
}
