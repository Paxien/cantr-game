import React from "react";
import ReactDOM from "react-dom";
import i18next from "i18next";
import {initReactI18next} from "react-i18next";
import "./index.scss";
import PlayerTopBarMicrofrontend from "./microfrontends/PlayerTopBarMicrofrontend";
import CharacterTopBarMicrofrontend from "./microfrontends/CharacterTopBarMicrofrontend";
import * as serviceWorker from "./serviceWorker";
import NavBarMicrofrontend from "./microfrontends/NavBarMicrofrontend";
import NewCharacterMicrofrontend from "./microfrontends/NewCharacterMicrofrontend";


declare global {
  interface Window {
    translations: object;
  }
}

i18next
  .use(initReactI18next)
  .init({
    lng: "en",
    debug: true,
    interpolation: {
      prefix: "#",
      suffix: "#",
      nestingPrefixEscaped: "<CANTR REPLACE NAME=",
      nestingSuffixEscaped: ">",
    },
    resources: {
      en: {
        translation: window.translations,
      }
    }
  });

const renderMicrofrontend = (containerId: string, Component: JSX.Element) => {
  if (document.getElementById(containerId)) {
    ReactDOM.render(
      <React.StrictMode>
        {Component}
      </React.StrictMode>,
      document.getElementById(containerId),
    );
  }
};

renderMicrofrontend("playerTopBar", <PlayerTopBarMicrofrontend/>);
renderMicrofrontend("characterTopBar", <CharacterTopBarMicrofrontend/>);
renderMicrofrontend("navBar", <NavBarMicrofrontend/>);
renderMicrofrontend("newCharacter", <NewCharacterMicrofrontend/>);

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
