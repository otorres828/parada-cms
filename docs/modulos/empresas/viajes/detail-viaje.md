# DetailViaje de Empresas

Extiende EmpresaComponent. La ruta `empresas.viajes.detail` exige viajes/detail mediante CheckPermissionEmpresa. El listado muestra el enlace solo con canDetail.

`mount` guarda el ID bloqueado, consulta el viaje mediante `findViaje()` y calcula los permisos para volver al listado y enlazar programaciones. `findViaje()` utiliza `Viaje::searchAdmin('', ['empresa_id' => $this->usuarioEmpresa->empresa_id])`, carga los terminales de la ruta y de sus combinaciones y termina en findOrFail. Una ruta ajena, eliminada o inexistente responde 404.

`render` vuelve a obtener la ruta por el mismo método y pagina su historial con `Programacion::searchDetailViajes`, también filtrado por empresa. Cada tabla tiene componentes propios en components/empresas/viajes. Se muestran recorrido, combinaciones con sus precios base y el historial de salidas. Las tasas se muestran según viewTasaServicio de EmpresaComponent. Los enlaces apuntan al panel Empresas y no al de Admin.

Prueba: tests/ViajesEmpresaSmoke.php. Verifica el detalle propio, rechazo de otra empresa, paginación, renderizado y findViaje de ambos paneles.
