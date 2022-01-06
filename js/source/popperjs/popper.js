import { popperGenerator, detectOverflow } from "./src/createPopper.js";
import eventListeners from "./src/modifiers/eventListeners.js";
import popperOffsets from "./src/modifiers/popperOffsets.js";
import computeStyles from "./src/modifiers/computeStyles.js";
import applyStyles from "./src/modifiers/applyStyles.js";
import offset from "./src/modifiers/offset.js";
import flip from "./src/modifiers/flip.js";
import preventOverflow from "./src/modifiers/preventOverflow.js";
import arrow from "./src/modifiers/arrow.js";
import hide from "./src/modifiers/hide.js";
var defaultModifiers = [eventListeners, popperOffsets, computeStyles, applyStyles, offset, flip, preventOverflow, arrow, hide];
var createPopper = /*#__PURE__*/popperGenerator({
  defaultModifiers: defaultModifiers
}); // eslint-disable-next-line import/no-unused-modules

export { createPopper, popperGenerator, defaultModifiers, detectOverflow }; // eslint-disable-next-line import/no-unused-modules

export { createPopper as createPopperLite } from "./src/popper-lite.js"; // eslint-disable-next-line import/no-unused-modules

export * from "./src/modifiers/index.js";