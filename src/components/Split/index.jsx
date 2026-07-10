import React from "react";

class Split extends React.Component {
  target = React.createRef();

  split = () => {
    if (typeof window === "undefined" || typeof Splitting === "undefined") {
      return;
    }
    if (this.target.current) {
      Splitting({ target: this.target.current });
    }
  };

  componentDidMount() {
    this.split();
    if (typeof Splitting === "undefined") {
      const retry = setInterval(() => {
        if (typeof Splitting !== "undefined") {
          this.split();
          clearInterval(retry);
        }
      }, 200);
      setTimeout(() => clearInterval(retry), 5000);
    }
  }

  render() {
    return <div ref={this.target}>{this.props.children}</div>;
  }
}

export default Split;
