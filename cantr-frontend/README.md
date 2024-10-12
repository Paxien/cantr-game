This project was bootstrapped with [Create React App](https://github.com/facebook/create-react-app).

# React microfrontends for Cantr

Because it would take too long time to rewrite all the old UI, this project was created as a possibility
to use modern technologies for creating client-side frontend alongside the old code.

These are not real microfrontends, but the cool name can make them more pleasant to work on :)

# Structure

One microfrontend represents a single div on the page, which is isolated from the rest of UI and managed by React.
It may make request calls to API, but communication with the rest of the page should be as limited as possible.

Because it's not fully possible, there is a need to have a component which serves as a intermediate layer
in communication with the oustide world. These components are named with -Microfrontend suffix and are stored in `microfrontends/` directory.

To register a newly created microfrontend you need to render it in `index.tsx`:

```ts
if (document.getElementById('playerTopBar')) {
  ReactDOM.render(
    <React.StrictMode>
      <PlayerTopBarMicrofrontend />
    </React.StrictMode>,
    document.getElementById('playerTopBar')
  )
}
```

It means `PlayerTopBarMicrofrontend` will be rendered in div with ID `playerTopBar` (if it exists).
To make it work you need to place `<div id="playerTopBar"></div>` somewhere in the Cantr Smarty template.

### Passing parameters from outside to the component

The initial values to start react should be passed through data- attribute
of the div being a container for a microfrontend.

```html
<div id="playerTopBar" data-day="6000" data-hour="3"></div>
```

Which can then be read in `PlayerTopBarMicrofrontend`:

```ts
  const playerTopBar = document.getElementById("playerTopBar");
  const initialInventoryWeight = Number(playerTopBar.dataset.inventoryWeight as string);
```

and used by react, for example as a default value for `useState`.
These data- attributes shouldn't change, because React won't notice it anyway.

The data should be unpacked in -Microfrontend component and the result passed down to components in a clean way.

### Subscribing to events from outside the container.

There's a hook called `useDomEvent`, which should be used only in -Microfrontend containers:

```ts
  useDomEvent("changeInventoryWeight", (event: { detail: number }) => {
    ...
  });
```

This creates a DOM Event Listener to `document.body`. If you need to send an event
from the script outside of React, for example from jQuery handler, then you can do it like that:

```js
document.body.dispatchEvent(new CustomEvent("changeInventoryWeight", {detail: 231}));
```

## Adding translations

Use react-i18next. TODO

# Available Scripts

### `yarn start`

Runs the app in the development mode.<br />
Open [http://localhost:3000](http://localhost:3000) to view it in the browser.

It's possible to see all the microfrontends by going there.
If any microfrontend requires any data from the outside world (translations, values from data- attributes), they can be supplied through `public/index.html`.

It is run as part of Cantr development by running docker-compose in `docker/` directory.
To use microfrontends served by devserver, you need to modify Cantr's `config/config.json`
and set "devserverMode" to "true" (check `config/config.default.json` for reference). Otherwise Cantr looks for static files in `www/react/` directory.

### `yarn build`

Builds the app for production to the `build` folder.<br />
It correctly bundles React in production mode and optimizes the build for the best performance.

### `yarn cantr-build`

Builds the app as in `yarn build` and copies it to directory `www/react/` to make it available
for old Cantr UI, which is rendered server-side by Smarty. The bundle is minified and chunked.

It's not necessary to rebuild index.tpl to see the newest version of the React code.