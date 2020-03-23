function prepareBlocks() {
	Blockly.Blocks.get_model_variable_boolean = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_boolean;
			} else {
				addition = keys_boolean_output;
			}
			this.appendDummyInput().appendField(new Blockly.FieldDropdown(addition), "modelvariables1");
			this.setOutput(true, "Boolean");
			this.setColour(210);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.set_model_variable_boolean = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_boolean;
			} else {
				addition = keys_boolean_input;
			}
			this.appendValueInput("NAME").appendField(Blockly.Msg["LISTS_SET_INDEX_SET"])
				.appendField(new Blockly.FieldDropdown(addition), "model variables1")
				.appendField(Blockly.Msg["TEXT_APPEND_TO"])
				.setCheck("Boolean");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(210);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.get_model_variable_number = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_number;
			} else {
				addition = keys_number_output;
			}
			this.appendDummyInput().appendField(new Blockly.FieldDropdown(addition), "modelvariables3");
			this.setOutput(true, "Number");
			this.setColour(230);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.set_model_variable_number = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_number;
			} else {
				addition = keys_number_input;
			}
			this.appendValueInput("NAME").appendField(Blockly.Msg["LISTS_SET_INDEX_SET"])
				.appendField(new Blockly.FieldDropdown(addition), "model variables3")
				.appendField(Blockly.Msg["TEXT_APPEND_TO"])
				.setCheck("Number");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(230);
			this.setTooltip('');
		}
	};


	/* EVENTS */
	Blockly.Blocks.set_model_variable_boolean2 = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_boolean;
			} else {
				addition = keys_boolean_input;
			}
			this.appendValueInput("NAME").appendField(Blockly.Msg["LISTS_SET_INDEX_SET"])
				.appendField(new Blockly.FieldDropdown(addition), "model variables1")
				.appendField(Blockly.Msg["TEXT_APPEND_TO"])
				.setCheck("Boolean");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(210);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.set_model_variable_number2 = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_number;
			} else {
				addition = keys_number_input;
			}
			this.appendValueInput("NAME").appendField(Blockly.Msg["LISTS_SET_INDEX_SET"])
				.appendField(new Blockly.FieldDropdown(addition), "model variables3")
				.appendField(Blockly.Msg["TEXT_APPEND_TO"])
				.setCheck("Number");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(230);
			this.setTooltip('');
		}
	};


	Blockly.Blocks.play_lab = {
		init: function () {
			this.appendDummyInput().appendField(Blockly.Msg["ExpSTART"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};


	Blockly.Blocks.pause_lab = {
		init: function () {
			this.appendDummyInput().appendField(Blockly.Msg["ExpPAUSE"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.reset_lab = {
		init: function () {
			this.appendDummyInput().appendField(Blockly.Msg["ExpRESET"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.initialize_lab = {
		init: function () {
			this.appendDummyInput()
				.appendField(Blockly.Msg["ExpINITIALIZE"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.wait = {
		init: function () {
			this.appendDummyInput().appendField(Blockly.Msg["ExpWAIT"])
				.appendField(new Blockly.FieldNumber(0, 0, 600), "TIME")
				.appendField(Blockly.Msg["ExpSECONDS"]);
			this.setPreviousStatement(true, "null");
			this.setNextStatement(true, "null");
			this.setColour(0);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['jsUpDown'] = {
		init: function () {
			this.appendDummyInput()
				.appendField("JS ")
				.appendField(new Blockly.FieldDropdown(visualJSGeneral), "jsOption");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(183);
			this.setTooltip("");
			this.setHelpUrl("");
		}
	};

	Blockly.Blocks['jsLeftRight'] = {
		init: function () {
			this.appendDummyInput()
				.appendField("JS ")
				.appendField(new Blockly.FieldDropdown(visualJSGeneral), "jsOption");
			this.setOutput(true, null);
			this.setColour(183);
			this.setTooltip("");
			this.setHelpUrl("");
		}
	};

	Blockly.Blocks['jsUpDown2'] = {
		init: function () {
			this.appendDummyInput()
				.appendField("JS ")
				.appendField(new Blockly.FieldDropdown(visualJSEvents), "jsOption");
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(183);
			this.setTooltip("");
			this.setHelpUrl("");
		}
	};

	Blockly.Blocks['jsLeftRight2'] = {
		init: function () {
			this.appendDummyInput()
				.appendField("JS ")
				.appendField(new Blockly.FieldDropdown(visualJSEvents), "jsOption");
			this.setOutput(true, null);
			this.setColour(183);
			this.setTooltip("");
			this.setHelpUrl("");
		}
	};


	Blockly.Blocks.start_rec = {
		init: function () {
			this.appendDummyInput()
				.appendField(Blockly.Msg["ExpSTARTRECORD"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(33);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.stop_rec = {
		init: function () {
			this.appendDummyInput()
				.appendField(Blockly.Msg["ExpSTOPRECORD"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(33);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.event = {
		init: function () {
			this.appendValueInput("NAME1").appendField(Blockly.Msg["ExpWHEN"])
				.appendField(new Blockly.FieldDropdown(events_vars), "events_vars")
				.appendField(Blockly.Msg["ExpIS"])
				.setCheck("Number");
			this.appendStatementInput("NAME2").setCheck(null)
				.appendField(Blockly.Msg["CONTROLS_REPEAT_INPUT_DO"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(60);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.fixedRelation = {
		init: function () {
			this.appendDummyInput().appendField(Blockly.Msg["ExpLABSTEP"]);
			this.appendStatementInput("NAME")
				.setCheck(null);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(60);
			this.setTooltip('');
		}
	};

	Blockly.Blocks['Y Axis'] = {
		init: function () {
			this.appendDummyInput()
				.appendField(Blockly.Msg["ExpYAXIS"]);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(200);
			this.setTooltip('');
		}
	};

	Blockly.Blocks.createChart = {
		init: function () {
			this.appendDummyInput().appendField(Blockly.Msg["ExpCHART"])
				.appendField(new Blockly.FieldTextInput("Title"), "Chart_NAME");
			this.appendDummyInput().appendField(Blockly.Msg["ExpCHARTOPTIONS"]);
			this.appendDummyInput().setAlign(Blockly.ALIGN_RIGHT)
				.appendField(Blockly.Msg["ExpCHARTPLOT"])
				.appendField(new Blockly.FieldNumber(100), "time")
				.appendField(Blockly.Msg["ExpMILLISECONDS"]);
			this.appendDummyInput().setAlign(Blockly.ALIGN_RIGHT)
				.appendField("   ")
				.appendField(new Blockly.FieldCheckbox("False"), "checkboxOnlyLast")
				.appendField(Blockly.Msg["ExpCHARTLAST"])
				.appendField(new Blockly.FieldNumber(10, 1), "numberOnlyLast")
				.appendField(Blockly.Msg["ExpVALUES"]);
			this.appendDummyInput().setAlign(Blockly.ALIGN_RIGHT)
				.appendField("   ")
				.appendField(new Blockly.FieldCheckbox("True"), "checkboxRenew")
				.appendField(Blockly.Msg["ExpCHARTRENEW"]);
			this.appendDummyInput().appendField(Blockly.Msg["ExpCHARTDATA"]);
			this.appendValueInput("x").setCheck(null)
				.setAlign(Blockly.ALIGN_RIGHT)
				.appendField(Blockly.Msg["ExpXAXIS"])
				.appendField(new Blockly.FieldTextInput("Label"), "cName0");
			this.appendValueInput('ADD-1')
				.appendField((0) + ". " + Blockly.Msg["ExpYAXIS"])
				.appendField(new Blockly.FieldTextInput("Label0"), "nameY-1")
				.setAlign(Blockly.ALIGN_RIGHT);
			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setInputsInline(false);
			this.setColour(200);
			this.setTooltip('');
			this.itemCount_ = 0;
			this.setMutator(new Blockly.Mutator(['Y Axis']));
		},
		/**
		 * Create XML to represent list inputs.
		 * @return {!Element} XML storage element.
		 * @this Blockly.Block
		 */
		mutationToDom: function () {
			var container = document.createElement('mutation');
			container.setAttribute('items', this.itemCount_);
			return container;
		},
		/**
		 * Parse XML to restore the list inputs.
		 * @param {!Element} xmlElement XML storage element.
		 * @this Blockly.Block
		 */
		domToMutation: function (xmlElement) {
			this.itemCount_ = parseInt(xmlElement.getAttribute('items'), 10);
			this.updateShape_();
		},
		/**
		 * Populate the mutator's dialog with this block's components.
		 * @param {!Blockly.Workspace} workspace Mutator's workspace.
		 * @return {!Blockly.Block} Root block in mutator.
		 * @this Blockly.Block
		 */
		decompose: function (workspace) {
			var containerBlock = workspace.newBlock('lists_create_with_container');
			containerBlock.initSvg();
			var connection = containerBlock.getInput('STACK').connection;
			for (var i = 0; i < this.itemCount_; i++) {
				var itemBlock = workspace.newBlock('lists_create_with_item');
				itemBlock.initSvg();
				connection.connect(itemBlock.previousConnection);
				connection = itemBlock.nextConnection;
			}
			return containerBlock;
		},
		/**
		 * Reconfigure this block based on the mutator dialog's components.
		 * @param {!Blockly.Block} containerBlock Root block in mutator.
		 * @this Blockly.Block
		 */
		compose: function (containerBlock) {
			var itemBlock = containerBlock.getInputTargetBlock('STACK');
			/* Count number of inputs. */
			var connections = [];
			while (itemBlock) {
				connections.push(itemBlock.valueConnection_);
				itemBlock = itemBlock.nextConnection && itemBlock.nextConnection.targetBlock();
			}
			/* Disconnect any children that don't belong. */
			for (var i = 0; i < this.itemCount_; i++) {
				var connection = this.getInput('ADD' + i).connection.targetConnection;
				if (connection && connections.indexOf(connection) === -1) {
					connection.disconnect();
				}
			}
			this.itemCount_ = connections.length;
			this.updateShape_();
			/* Reconnect any child blocks. */
			for (var i = 0; i < this.itemCount_; i++) {
				Blockly.Mutator.reconnect(connections[i], this, 'ADD' + i);
			}
		},
		/**
		 * Store pointers to any connected child blocks.
		 * @param {!Blockly.Block} containerBlock Root block in mutator.
		 * @this Blockly.Block
		 */
		saveConnections: function (containerBlock) {
			var itemBlock = containerBlock.getInputTargetBlock('STACK');
			var i = 0;
			while (itemBlock) {
				var input = this.getInput('ADD' + i);
				itemBlock.valueConnection_ = input && input.connection.targetConnection;
				i++;
				itemBlock = itemBlock.nextConnection &&
					itemBlock.nextConnection.targetBlock();
			}
		},
		/**
		 * Modify this block to have the correct number of inputs.
		 * @private
		 * @this Blockly.Block
		 */
		updateShape_: function () {
			if (this.itemCount_ && this.getInput('EMPTY')) {
				this.removeInput('EMPTY');
			}
			/* Add new inputs. */
			for (var i = 0; i < this.itemCount_; i++) {
				if (!this.getInput('ADD' + i)) {
					var input = this.appendValueInput('ADD' + i);
					input.appendField((i + 1) + ". " + Blockly.Msg["ExpYAXIS"]);
					input.appendField(new Blockly.FieldTextInput("Label" + (i + 1)), "nameY" + i);
					input.setAlign(Blockly.ALIGN_RIGHT);
				}
			}
			/* Remove deleted inputs. */
			while (this.getInput('ADD' + i)) {
				this.removeInput('ADD' + i);
				i++;
			}
		}
	};

	Blockly.Blocks.record_var = {
		init: function () {
			var addition;
			if (!newImplement) {
				addition = keys_number;
			} else {
				addition = keys_number_input;
			}
			this.appendDummyInput()
				.appendField("record")
				.appendField(new Blockly.FieldDropdown(addition), "model variables3")
				.appendField("changes in reference to")
				.appendField(new Blockly.FieldDropdown(addition), "model variables4")

			this.setPreviousStatement(true, null);
			this.setNextStatement(true, null);
			this.setColour(200);
			this.setTooltip('');
		}
	};


	// FUNCIONES EJSS
	for (var i = 0; i < function_from_ejss.length; i++) {
		Blockly.Blocks['ejss_procedures_callnoreturn' + function_from_ejss[i].name] = {
			init: function () {
				var tipo = this.type.replace("ejss_procedures_callnoreturn", "");
				for (var i = 0; i < function_from_ejss.length; i++) {
					if(function_from_ejss[i].name.localeCompare(tipo)==0){
						if (function_from_ejss[i].params.length == 0) {
							this.appendDummyInput()
								.appendField(tipo);
						} else {
							this.appendDummyInput()
								.appendField(tipo)
								.appendField(Blockly.Msg["PROCEDURES_CALL_BEFORE_PARAMS"]);
							for (var j = 0; j < function_from_ejss[i].params.length; j++) {
								this.appendValueInput("NAME" + j)
									.setCheck(null)
									.setAlign(Blockly.ALIGN_RIGHT)
									.appendField(function_from_ejss[i].params[j]);
							}
						}
					}
				}

				this.setPreviousStatement(true, null);
				this.setNextStatement(true, null);
				this.setColour(290);
				this.setTooltip("");
				this.setHelpUrl("");
			}
		};
	}



	for (var i = 0; i < function_from_ejss_with_return.length; i++) {

		Blockly.Blocks['ejss_procedures_callwithreturn' + function_from_ejss_with_return[i].name] = {
			init: function () {

				var tipo = this.type.replace("ejss_procedures_callwithreturn", "");
				for (var i = 0; i < function_from_ejss_with_return.length; i++) {
					if (function_from_ejss_with_return[i].name.localeCompare(tipo) == 0) {
						if (function_from_ejss_with_return[i].params.length == 0) {
							this.appendDummyInput()
								.appendField(tipo);
						} else {
							this.appendDummyInput()
								.appendField(tipo)
								.appendField(Blockly.Msg["PROCEDURES_CALL_BEFORE_PARAMS"]);
							for (var j = 0; j < function_from_ejss_with_return[i].params.length; j++) {
								this.appendValueInput("NAME" + j)
									.setCheck(null)
									.setAlign(Blockly.ALIGN_RIGHT)
									.appendField(function_from_ejss_with_return[i].params[j]);
							}
						}
					}
				}
				this.setOutput(true, null);
				this.setColour(290);
				this.setTooltip("");
				this.setHelpUrl("");
			}
		};
	}
}
