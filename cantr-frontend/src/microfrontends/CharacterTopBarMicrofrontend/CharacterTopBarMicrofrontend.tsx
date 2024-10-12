import React, {useState} from 'react';
import {useDomEvent} from "../util";
import CharacterNavBar from "../../components/CharacterNavBar";

/**
 * This component is just an example how to abstract React components from the non-React world by Microfrontend intermediate layer.
 * It's not used anywhere.
 */
const CharacterTopBarMicrofrontend = () => {
  const characterTopBar = document.getElementById("characterTopBar");
  const initialInventoryWeight = characterTopBar ? Number(characterTopBar.dataset.inventoryWeight as string) : 0;
  const [inventoryWeight, setInventoryWeight] = useState(initialInventoryWeight);
  useDomEvent("changeInventoryWeight", (event: { detail: number }) => {
    setInventoryWeight(inventoryWeight + event.detail);
  });
  return (
    <div className="react-component">
      <CharacterNavBar inventoryWeight={inventoryWeight}/>
    </div>
  );
}

export default CharacterTopBarMicrofrontend;
