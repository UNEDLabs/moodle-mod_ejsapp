function prepareToolBox() {
    require(['mod_ejsapp/vendor/blockly/blockly_compressed','mod_ejsapp/vendor/blockly/blockly_compressed'], function(Blockly) {
       toolbox = '<xml>' +
            '  <category name="' + Blockly.NameLogic + '" colour="210">' +
            '      <block type="controls_if"></block>' +
            '      <block type="logic_compare"></block>' +
            '      <block type="logic_operation"></block>' +
            '      <block type="logic_negate"></block>' +
            '      <block type="logic_boolean"></block>' +
            '      <block type="logic_null"></block>' +
            '      <block type="logic_ternary"></block>' +
            '    </category>' +
            '    <category name="' + Blockly.NameLoops + '" colour="120">' +
            '      <block type="controls_repeat_ext">' +
            '        <value name="TIMES">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="controls_whileUntil"></block>' +
            '      <block type="controls_for">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="BY">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="controls_forEach"></block>' +
            '      <block type="controls_flow_statements"></block>' +
            '    </category>' +
            '    <category name="' + Blockly.NameMath + '" colour="230">' +
            '      <block type="math_number"></block>' +
            '      <block type="math_arithmetic">' +
            '        <value name="A">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="B">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_single">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">9</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_trig">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">45</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constant"></block>' +
            '      <block type="math_number_property">' +
            '        <value name="NUMBER_TO_CHECK">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">0</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_round">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">3.1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_on_list"></block>' +
            '      <block type="math_modulo">' +
            '        <value name="DIVIDEND">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">64</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="DIVISOR">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constrain">' +
            '        <value name="VALUE">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">50</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="LOW">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="HIGH">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_int">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_float"></block>' +
            '    </category>' +
            '    <category name="JavaScript" custom="jss" colour="183"></category>' +
            '    <sep></sep>' +
            '    	<category name="Variables" custom="generalVars" colour="44"></category>' +
            '       <category name="' + Blockly.NameFunctions + '" colour="290" custom="PROCEDURE"></category>' +
            '   <category name="' + Blockly.Msg["EJSSFunctions"] + '" colour="290" custom="ejssFunctions" ></category>' +
            '    <sep></sep>' +
            '   		 <category name="' + Blockly.NameExecution + '" colour = "0">' +
            '   		 	<block type=\"play_lab\"></block><block type=\"pause_lab\"></block>' +
            '   		 	<block type=\"initialize_lab\"></block><block type=\"reset_lab\"></block>' +
            '   		 	<block type=\"wait\"></block>' +
            '   		 </category>' +
            '		 <category name="' + Blockly.NameData + '" colour="33">' +
            '   		 	<block type="start_rec"></block>' +
            '   		 	<block type="stop_rec"></block>' +
            '   		 </category>' +
            '</xml>';


        toolboxEvents = '<xml>' +
            '  <category name="' + Blockly.NameEvents + '"  custom="controls" colour = "60">  </category>' +
            '    <sep></sep>' +
            '  <category name="' + Blockly.NameLogic + '" colour="210">' +
            '      <block type="controls_if"></block>' +
            '      <block type="logic_compare"></block>' +
            '      <block type="logic_operation"></block>' +
            '      <block type="logic_negate"></block>' +
            '      <block type="logic_boolean"></block>' +
            '      <block type="logic_null"></block>' +
            '      <block type="logic_ternary"></block>' +
            '    </category>' +
            '    <category name="' + Blockly.NameLoops + '" colour="120">' +
            '      <block type="controls_repeat_ext">' +
            '        <value name="TIMES">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="controls_whileUntil"></block>' +
            '      <block type="controls_for">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="BY">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="controls_forEach"></block>' +
            '      <block type="controls_flow_statements"></block>' +
            '    </category>' +
            '    <category name="' + Blockly.NameMath + '" colour="230">' +
            '      <block type="math_number"></block>' +
            '      <block type="math_arithmetic">' +
            '        <value name="A">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="B">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_single">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">9</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_trig">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">45</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constant"></block>' +
            '      <block type="math_number_property">' +
            '        <value name="NUMBER_TO_CHECK">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">0</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_round">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">3.1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_on_list"></block>' +
            '      <block type="math_modulo">' +
            '        <value name="DIVIDEND">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">64</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="DIVISOR">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constrain">' +
            '        <value name="VALUE">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">50</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="LOW">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="HIGH">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_int">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_float"></block>' +
            '    </category>' +
            '    <category name="JavaScript" custom="jss2" colour="183"></category>' +
            '    <sep></sep>' +
            '    	<category name="Variables" custom="generalVars" colour="44"></category>' +
            '       <category name="' + Blockly.NameFunctions + '" colour="290" custom="PROCEDURE"></category>' +
            '    <sep></sep>' +
            '   		 <category name="' + Blockly.NameExecution + '" colour = "0">' +
            '   		 	<block type=\"play_lab\"></block><block type=\"pause_lab\"></block>' +
            '   		 	<block type=\"initialize_lab\"></block><block type=\"reset_lab\"></block>' +
            '   		 	<block type=\"wait\"></block>' +
            '   		 </category>' +
            '		 <category name="' + Blockly.NameData + '" colour="33">' +
            '   		 	<block type="start_rec"></block>' +
            '   		 	<block type="stop_rec"></block>' +
            '   		 </category>' +
            '</xml>';


        toolboxCharts = '<xml>' +
            '	<category name="' + Blockly.NameCharts + '" colour="200">' +
            '      <block type="createChart"></block>' +
            '      <block type="record_var"></block>' +
            '   		 </category>' +
            '    <category name="' + Blockly.NameMath + '" colour="230">' +
            '      <block type="math_number"></block>' +
            '      <block type="math_arithmetic">' +
            '        <value name="A">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="B">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_single">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">9</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_trig">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">45</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constant"></block>' +
            '      <block type="math_number_property">' +
            '        <value name="NUMBER_TO_CHECK">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">0</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_round">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">3.1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_on_list"></block>' +
            '      <block type="math_modulo">' +
            '        <value name="DIVIDEND">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">64</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="DIVISOR">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constrain">' +
            '        <value name="VALUE">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">50</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="LOW">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="HIGH">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_int">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_float"></block>' +
            '    </category>' +
            '    <sep></sep>' +
            '    	<category name="Variables" custom="generalVars" colour="44"></category>' +
            '</xml>';


        toolboxControllers = '<xml>' +
            '  <category name="' + Blockly.NameLogic + '" colour="210">' +
            '      <block type="controls_if"></block>' +
            '      <block type="logic_compare"></block>' +
            '      <block type="logic_operation"></block>' +
            '      <block type="logic_negate"></block>' +
            '      <block type="logic_boolean"></block>' +
            '      <block type="logic_null"></block>' +
            '      <block type="logic_ternary"></block>' +
            '    </category>' +
            '    <category name="' + Blockly.NameLoops + '" colour="120">' +
            '      <block type="controls_repeat_ext">' +
            '        <value name="TIMES">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="controls_whileUntil"></block>' +
            '      <block type="controls_for">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="BY">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="controls_forEach"></block>' +
            '      <block type="controls_flow_statements"></block>' +
            '    </category>' +
            '    <category name="' + Blockly.NameMath + '" colour="230">' +
            '      <block type="math_number"></block>' +
            '      <block type="math_arithmetic">' +
            '        <value name="A">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="B">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_single">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">9</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_trig">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">45</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constant"></block>' +
            '      <block type="math_number_property">' +
            '        <value name="NUMBER_TO_CHECK">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">0</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_round">' +
            '        <value name="NUM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">3.1</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_on_list"></block>' +
            '      <block type="math_modulo">' +
            '        <value name="DIVIDEND">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">64</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="DIVISOR">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">10</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_constrain">' +
            '        <value name="VALUE">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">50</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="LOW">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="HIGH">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_int">' +
            '        <value name="FROM">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">1</field>' +
            '          </shadow>' +
            '        </value>' +
            '        <value name="TO">' +
            '          <shadow type="math_number">' +
            '            <field name="NUM">100</field>' +
            '          </shadow>' +
            '        </value>' +
            '      </block>' +
            '      <block type="math_random_float"></block>' +
            '    </category>' +
            '    <sep></sep>' +
            '    	<category name="Variables" custom="generalVars" colour="44"></category>' +
            '</xml>';
    });
}