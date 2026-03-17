<form name="nuevo" method="post" action="/sistema/anexo27/frmanexo27c.php">
    <table width="57%" border="0" align="center" cellpadding="0" cellspacing="0" class="formulario">
        <tbody><tr>
            <td colspan="4"><input type="hidden" name="id_anexo" id="id_anexo" value="3594037">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr height="20px">
            <td colspan="2">&nbsp;&nbsp;<strong>Fecha transferencia:</strong></td>
            <td colspan="2">&nbsp;&nbsp;<strong> Nomenclatura del Expediente:</strong></td>
        </tr>
        <tr height="35px">
            <td colspan="2">&nbsp;
                <input name="fecha_trans" type="text" id="fecha_trans" value="19-02-2026" maxlength="10">
                <script language="JavaScript"> new tcal ({'formname': 'nuevo','controlname': 'fecha_trans'});</script><img src="../img/cal.gif" id="tcalico_0" onclick="A_TCALS['0'].f_toggle()" class="tcalIcon" alt="Open Calendar">
            </td>
            <td colspan="2">&nbsp;
                <input name="expediente" type="text" id="expediente" size="55" maxlength="70" value="SES/4C.12/03/2024"></td>
            <td width="5"></td>
        </tr>
        <tr height="35px">
            <td colspan="4">&nbsp;<strong> Descripción del Expediente:</strong></td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;
                <textarea name="descripcion" cols="95" rows="4" id="descripcion">SICAPRON</textarea></td>
        </tr>
        <tr height="35px">
            <td><strong>&nbsp;&nbsp;Año de creación:</strong></td>
            <td>&nbsp;</td>
            <td colspan="1"><strong>&nbsp;&nbsp;Período: Del </strong></td>
            <td colspan="1"><strong>&nbsp;&nbsp;Al: </strong></td>
        </tr>
        <tr height="35px">
            <td colspan="1">&nbsp;<input name="antiguedad" type="text" id="antiguedad" size="25" maxlength="25" value="2024"></td>
            <td></td>
            <td>&nbsp; <label>
                    <input name="per_del" type="text" id="per_del" value="09-01-2024" maxlength="10">
                </label>
                <script language="JavaScript"> new tcal ({'formname': 'nuevo','controlname': 'per_del'});</script><img src="../img/cal.gif" id="tcalico_1" onclick="A_TCALS['1'].f_toggle()" class="tcalIcon" alt="Open Calendar">
            </td>
            <td>&nbsp; <label><input name="per_al" type="text" id="per_al" value="27-11-2024" maxlength="10">
                </label>
                <script language="JavaScript"> new tcal ({'formname': 'nuevo','controlname': 'per_al'});</script><img src="../img/cal.gif" id="tcalico_2" onclick="A_TCALS['2'].f_toggle()" class="tcalIcon" alt="Open Calendar">
            </td>
        </tr>
        <tr height="35px">
            <td>&nbsp;<strong>Tiempo de conservación:</strong></td>
            <td>&nbsp;</td>
            <td><strong>&nbsp;&nbsp;No. Legajos</strong></td>
            <td><strong>&nbsp;&nbsp;No. Hojas</strong></td>
        </tr>
        <tr height="35px">
            <td>&nbsp;<input name="tiempo_conservacion" type="text" id="tiempo_conservacion" size="25" maxlength="20" value="6"></td>
            <td>&nbsp;</td>
            <td>&nbsp;<input name="n_legajos" type="text" id="n_legajos" size="10" maxlength="10" value="1"></td>
            <td>&nbsp;<input name="n_hojas" type="text" id="n_hojas" size="10" maxlength="10" value="262"></td>
        </tr>
        <tr height="35px">
            <td><strong>&nbsp;&nbsp;Preservación:</strong></td>
            <td>&nbsp;</td>
            <td colspan="2"><strong>&nbsp;Ubicación Topográfica:</strong></td>
        </tr>
        <tr height="35px">
            <td colspan="2">&nbsp;&nbsp;<select name="preservacion">
                    <option value="S">SI</option><option value="N" selected="">NO</option></select>
                <div class="mensaje-error-default">&nbsp;Seleccion Actual: NO</div></td>
            <td colspan="2">&nbsp;<input name="localizacion" type="text" id="localizacion" size="60" maxlength="70" value="SISTEMATIZACIóN DE PAGO"></td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr height="35px">
            <td><strong>&nbsp;&nbsp;No. de Caja</strong></td>
            <td>&nbsp;</td>
            <td><strong>&nbsp;Clasificación:</strong></td>
            <td><strong>&nbsp;Caracter Documental:</strong></td>
        </tr>
        <tr height="30px">
            <td>&nbsp;&nbsp;<input name="no_caja" type="text" id="no_caja" size="20" maxlength="20" value="1"></td>
            <td>&nbsp;</td>
            <td><select name="clasificacion">
                    <option value="P" selected="">PÚBLICO</option><option value="R">RESERVADO</option><option value="C">CONFIDENCIAL</option><option value="X">SIN CLASIFICACION</option></select>
                <div class="mensaje-error-default">&nbsp;Seleccion Actual: PÚBLICO</div></td>
            <td><select name="caracter">
                    <option value="L">LEGAL</option><option value="A" selected="">ADMINISTRATIVO</option><option value="F">FISCAL</option><option value="X">SIN CARACTER</option></select>
                <div class="mensaje-error-default">&nbsp;Seleccion Actual: ADMINISTRATIVO</div></td>
        </tr>
        <tr height="35px">
            <td colspan="2">&nbsp;<strong> Observaciones:</strong></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4">&nbsp;
                <textarea name="observaciones" cols="95" rows="3" id="observaciones"></textarea></td>
        </tr>
        <tr height="35px">
            <td colspan="4" align="center"></td>
        </tr>
        <tr>
            <td colspan="4"><div align="center">
                    <input name="modificar" type="submit" id="modificar" value="Guardar Cambios" class="estilo1">&nbsp;
                    <input type="button" name="btnEliminar" value="Eliminar" onclick="Eliminar('3594037','SES/4C.12/03/2024');" class="estilo1">
                    &nbsp; <input name="cancelar" type="button" id="cancelar" value="Cancelar" class="estilo1" onclick="javascript:location.href='anexo27.php?tipo_anexo27=anexo27c&amp;page=&amp;orden='; ">
                    <input type="hidden" name="page" id="page" value="">
                    <input type="hidden" name="orden" id="page" value="">

                    &nbsp;
                    <input type="button" name="btnNuevo" value="Nueva Captura" onclick="javascript:location.href='frmanexo27c.php?accion=nuevo'; " class="estilo1"><br><br>
                </div></td>
        </tr>
        </tbody></table>
</form>
