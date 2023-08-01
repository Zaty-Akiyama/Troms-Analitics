const analiticsInit = () => {

  const form = document.querySelector( '.jsAnaliticsForm' );
  const defaultButton = document.querySelector( '.jsDefaultButton' );
  defaultButton.addEventListener( 'click', e => {
    const defaultInput = document.createElement( 'input' );
    defaultInput.type = 'hidden';
    defaultInput.name = 'default';
    defaultInput.value = '1';
    form.appendChild( defaultInput );

    form.submit();
  });

  const startInput = document.querySelector( '.jsStartInput' );
  const endInput = document.querySelector( '.jsEndInput' );
  startInput.addEventListener( 'change', () => {
    endInput.min = startInput.value;
  });
  endInput.addEventListener( 'change', () => {
    startInput.max = endInput.value;
  });

  const table = document.querySelector(".jsAnaliticsTable");
  const tbody = table.querySelector("tbody");
  const rowsArray = Array.prototype.slice.call(tbody.rows);

  const sort = ( rowArray, order = false ) => {
    // 配列を並び替え
    rowsArray.sort(function(a, b) {
      const minus = order ? -1 : 1;
      return minus * ( parseInt(b.cells[2].innerText) - parseInt(a.cells[2].innerText) );
    });

    rowsArray.forEach(function(row) {
      tbody.appendChild(row);
    });
  }
  sort( rowsArray );

  const pvSortButton = document.querySelector( '.jsPvSortButton' );
  pvSortButton.addEventListener( 'click', () => {
    const isActive = pvSortButton.classList.toggle( 'is-active' );

    sort( rowsArray, isActive );
  });
}

window.addEventListener( 'load', analiticsInit, false );