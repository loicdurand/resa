export default class Periode {

  periode = 'from';
  #other = 'to';
  #onchange = () => void (0);

  constructor() {
    return this;
  }

  get periode() {
    return this.periode;
  }

  get() {
    return this.periode;
  }

  set(value) {
    this.#setPeriode(value);
  }

  toggle() {
    this.periode = this.periode === 'from' ? 'to' : 'from';
    this.#setOther(this.periode === 'from' ? 'to' : 'from');
    return this;
  }

  #setPeriode(value) {
    this.periode = value;
    this.#setOther(value === 'from' ? 'to' : 'from');
    return this;
  }

  get other() {
    return this.#other;
  }

  #setOther(value) {
    this.#other = value;
    return this;
  }

  setListeners(listeners) {
    listeners.forEach(({ elt, evt = 'click', cb }) => {
      elt.addEventListener(evt, (e) => {
        cb(this, e);
        this.#onchange(this, e);
      });
    });
    return this;
  }

  onClickEvents(listeners) {
    return this.setListeners(listeners);
  }

  then(fn) {
    this.#onchange = () => fn(this);
    return this;
  }

}