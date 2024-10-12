import React from "react";
import Button from "react-bootstrap/Button";
import Navbar from "react-bootstrap/Navbar";
import Nav from "react-bootstrap/Nav";
import Form from "react-bootstrap/Form";
import FormControl from "react-bootstrap/FormControl";

type Props = {
  inventoryWeight: number;
}

const CharacterNavBar: React.FC<Props> = ({inventoryWeight}) => {
  return (
    <Navbar fixed="top" bg="dark" variant="dark">
      <Navbar.Brand href="#home">Navbar</Navbar.Brand>
      <Nav className="mr-auto">
        <Nav.Link href="#home">gaweq</Nav.Link>
        <Nav.Link href="#features">Carrying: {inventoryWeight}g</Nav.Link>
      </Nav>
      <Form inline>
        <FormControl type="text" placeholder="Search" className="mr-sm-2"/>
        <Button variant="outline-info">Search</Button>
      </Form>
    </Navbar>
  );
};

export default CharacterNavBar;
