<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Listar clientes
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_razon_social', 'like', "%{$buscar}%")
                  ->orWhere('numero_documento', 'like', "%{$buscar}%");
            });
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $clientes = $query->orderBy('nombre_razon_social')->paginate(20);

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Almacenar nuevo cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE,Pasaporte',
            'numero_documento' => 'required|string|max:20|unique:clientes,numero_documento',
            'nombre_razon_social' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:50',
            'actividad_economica' => 'nullable|string|max:255',
        ]);

        $cliente = Cliente::create($validated);

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente creado exitosamente');
    }

    /**
     * Mostrar cliente
     */
    public function show(int $id)
    {
        $cliente = Cliente::with('recibos')->findOrFail($id);

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(int $id)
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, int $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE,Pasaporte',
            'numero_documento' => 'required|string|max:20|unique:clientes,numero_documento,' . $id,
            'nombre_razon_social' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:50',
            'actividad_economica' => 'nullable|string|max:255',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Eliminar cliente
     */
    public function destroy(int $id)
    {
        $cliente = Cliente::findOrFail($id);

        // Verificar que no tenga recibos
        if ($cliente->recibos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un cliente con recibos registrados');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente eliminado exitosamente');
    }
}
