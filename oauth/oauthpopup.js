$.oAuthPopup = function (opts) {
  opts.name = opts.name ||  '_blank';
  opts.specs = opts.specs || 'location=0,status=0,width=500,height=600';
  opts.callback = opts.callback || function () {
    window.location.reload();
  };

  var instance = this;
  instance.oAuthWindow = window.open(opts.path, opts.name, opts.specs);
  instance.oAuthInterval = window.setInterval(function () {
    if (instance.oAuthWindow && instance.oAuthWindow.closed) {
      window.clearInterval(instance.oAuthInterval);
      opts.callback();
    }
  }, 1000);
};
