import React from 'react';
import PlayerNavBar from "../../components/PlayerNavBar";

/**
 * This component is just an example how to abstract React components from the non-React world by Microfrontend intermediate layer.
 * It's not used anywhere.
 */
const PlayerTopBarMicrofrontend = () => (
  <div className="react-component">
    <PlayerNavBar/>
  </div>
);

export default PlayerTopBarMicrofrontend;
